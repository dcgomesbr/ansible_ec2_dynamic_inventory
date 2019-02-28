# ansible_ec2_dynamic_inventory
Some experiments with Ansible, ec2.py, AWS EC2 and dynamic inventory techniques

 2019-02-28: Yay! Finally, the project is complete! It will install a load balanced WordPress between two EC2 Instances.

## Pre-requisites:

This was built to work out-of-the-box for Linux and macOS.

If you use Windows for DevOps, you're doing it wrong.

### AWS Business as Usual
  * AWS account capable of creating EC2 free tier eligible instances, RDS and CloudFront
  * AWS CLI installed and configured with a working aws_access_key_id and aws_secret_access_key

  I'm NOT using ansible-vault here, keys will come from ~/.aws/credentials

### Python modules
  * Ansible
  * Boto
  * Boto3

    In inventory/base file, I setup ansible_python_interpreter parameter poiting to where my
    python binary is. This can be necessary if you have multiple Python installations conflicting
    your OS (MacOS wasn't nice about it).

### SSH stuff
  * OpenSSH / ssh-agent

    ssh-agent configured with the AWS key pair for passworless authentication, adding it with ssh-add;

  * AWS PEM file

    PEM file downloaded from AWS and configured in ansible.cfg at private_key_file parameter.
    Generate the keypair in the AWS Web Console normally and download the PEM file, placing it in keys directory

### ec2.py scripts
  * Although it's in my repo's inventory directory, you can download the latest from:

  https://raw.githubusercontent.com/ansible/ansible/devel/contrib/inventory/ec2.ini

  https://raw.githubusercontent.com/ansible/ansible/devel/contrib/inventory/ec2.py

  They must go inside inventory directory, because in ansible.cfg it points inventory = inventory/

  All the stuff above and how to make it work is broadly covered so just Google for it.

## Purpose:

  Experiment with provisioning and maintaining EC2 fleets and services using solely Ansible and Boto, making use of dynamic machine inventories instead of a static hosts file.


## How to use
### VPC, Subnets, NAT Gateways, Security Groups
* Setup the VPC for your app's EC2 instances

  Let's say you want to do a Wordpress installation isolated in a private network, served by a ELB or CDN

  Notice that it makes use of networks/WordpressVPC.yml config file for the VPC vars

  If ec2_vpc_id variable might be overwritten, but it must exist in the file
  You can set it up as none if you wish.
  ec2_vpc_id: none

  `ansible-playbook vpc_setup_vpc.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC"`

* Create the subnets. Let's create the Public subnet that will have a jump box (bastion)

  `ansible-playbook vpc_setup_subnet.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC ec2_subnet=PublicSubnetBastion"`

  Notice that it uses the network/PublicSubnetBastion.yml, same deal as before. Vars will be replaced with most current content, but they must exist prior to calling the script

  This script also creates Internet Gateways and Routes if specified to be a public subnet

  More subnets, for the WordPress hosts, RDS and App Load Balancer

  `ansible-playbook vpc_setup_subnet.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC ec2_subnet=PrivateSubnetDB01"`
  `ansible-playbook vpc_setup_subnet.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC ec2_subnet=PrivateSubnetDB02"`
  `ansible-playbook vpc_setup_subnet.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC ec2_subnet=PrivateSubnetWP01"`
  `ansible-playbook vpc_setup_subnet.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC ec2_subnet=PrivateSubnetWP02"`
  `ansible-playbook vpc_setup_subnet.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC ec2_subnet=PublicSubnetLB01"`
  `ansible-playbook vpc_setup_subnet.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC ec2_subnet=PublicSubnetLB02"`

* Create the Security Group so you don't instantiate the EC2 into a default one

  This security group will allow us to SSH into it, run a yum update and etc...
  It will be restricted to my machine's IP only, which is collected real time during the SG creation.

  There is a script you can use later to update your IP across all your machines by role, but you need instances up in AdminJumpbox role: ec2_update_admin_ipaddr.yml

  `ansible-playbook vpc_create_security_group.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC ec2_sg=AdminSecurityGroup"`

 A SG for the WP servers so they can be accessed through the Bastion via SSH and open 80 and 443 to the Load Balancers.
  `ansible-playbook vpc_create_security_group.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC ec2_sg=WPServerSecurityGroup"`

 A SG for the Load Balancer too:
  `ansible-playbook vpc_create_security_group.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC ec2_sg=LBSecurityGroup"`

 A SG for the RDS so app can access them
 `ansible-playbook vpc_create_security_group.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC ec2_sg=RDSSecurityGroup"`

* Create a NAT gateway

  This is useful for your instances behind a private network to have Internet access shielded by a NAT gateway so they can package installs and updates - this will create just one NAT Gateway and setup the outgoing routes for both WP Private Subnets

 `ansible-playbook vpc_setup_nat_gw.yml -e "ec2_region=us-east-1 ec2_vpc=WordpressVPC ec2_private_subnet=PrivateSubnetWP01 ec2_public_subnet=PublicSubnetBastion"`
 `ansible-playbook vpc_setup_nat_gw.yml -e "ec2_region=us-east-1 ec2_vpc=WordpressVPC ec2_private_subnet=PrivateSubnetWP02 ec2_public_subnet=PublicSubnetBastion"`

 The NAT gateway is created in the public subnet and allocates an Elastic IP (EIP) for it. Then, in the private subnet, you create a default route for it to addresses outside your subnets. The Security Groups must allow this, keep that in mind...

 Keep in mind that this thing costs MONEY. Shut it down once you're done installing packages in your instances and release the Elastic IP.

### Instantiate the RDS MySQL
 `ansible-playbook rds_create_instance.yml -e "ec2_region=us-east-1 rds_role=WordPressDB ec2_sg=RDSSecurityGroup"`

### Instantiate the EC2

* Instantiate the Admin Jumpbox, a.k.a. bastion

  `ansible-playbook ec2_provision_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=AdminJumpbox ec2_sg=AdminSecurityGroup"`

  The ec2_sg is optional, but since I want to demonstrate the SSH functionality, I'll put this instance in a security group that enables SSH access from my IP. Remember to setup they keypair PEM in AWS EC2.

* Updates the IP address of the bastion in the WPServer role configuration

  `ansible-playbook ec2_update_bastion_address.yml -e "ec2_region=us-east-1 ec2_role=WPServer ec2_bastion_role=AdminJumpbox"`


* Instantiate the WPServer, a.k.a. the web server

  `ansible-playbook ec2_provision_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=WPServer ec2_sg=WPServerSecurityGroup"`

  Since we have two subnets for this role, it will place one instance in each

* Perform a yum update on all instances of that same role

  Now we can install and update all Linux software in these instances at once by role

    * Updates:

    `ansible-playbook ec2_yum_update_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=AdminJumpbox"`

    `ansible-playbook ec2_yum_update_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=WPServer"`

    * Installation:

    `ansible-playbook ec2_yum_install_packages_by_role.yml -e "ec2_region=us-east-1 ec2_role=WPServer"`

    * Install WordPress and health check index.html
    `ansible-playbook ec2_install_wp_by_role.yml -e "ec2_region=us-east-1 ec2_role=WPServer"`

* Create TargetGroup and App LoadBalancer for WPServer

  `ansible-playbook ec2_lb_create_by_role.yml -e "ec2_region=us-east-1 ec2_role=WPServer lb_security_group=LBSecurityGroup"`

### Checks and clean up

* List all instances

  `ansible-playbook ec2_list_metadata.yml`

  `ec2_list_all.yml`

 * Ping all instances by role

 `ansible-playbook ec2_ping_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=AdminJumpbox"`

 `ansible-playbook ec2_ping_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=WPServer"`

* Terminate all instances by role

  Let's terminate this instance so we don't risk paying AWS money overnight

  `ansible-playbook ec2_term_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=AdminJumpbox"`

  `ansible-playbook ec2_term_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=WPServer"`

* Delete the NAT Gateway and release the EIP
 `ansible-playbook vpc_delete_nat_gateway.yml -e "ec2_region=us-east-1 ec2_subnet=PublicSubnetBastion"`

## TODO:
  - [x] Basic Instantiation scripts
  - [x] EC2 roles for a bastion and webserver
  - [x] VPC, subnet and NAT Gateway scripts
  - [x] Yum package scripts
  - [x] App Load Balancer instantiation and configuration
  - [x] A script to remove NAT Gateways and free EIP
  - [x] Wordpress installation script
  - [x] RDS instantiation and configuration
