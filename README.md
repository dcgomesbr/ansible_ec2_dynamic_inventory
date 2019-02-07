# ansible_ec2_dynamic_inventory
Some experiments with Ansible, ec2.py, AWS EC2 and dynamic inventory techniques

## Pre-requisites:
### AWS Business as Usual
  * AWS account capable of creating EC2 free tier eligible instances, RDS and CloudFront
  * AWS CLI installed and configured with a working aws_access_key_id and aws_secret_access_key
  
  I'm NOT using ansible-vault here, keys will come from ~/.aws/credentials

### Python modules
  * Ansible
  * Boto
  * Boto3
     
    In inventory/base file, I setup ansible_python_interpreter parameter poiting to where my
    python binary is. This can be necessary if you have multiple Python instalations conflicting
    your OS (MacOS wasn't nice about it).

### SSH stuff
  * OpenSSH / ssh-agent
  
    ssh-agent configured with the AWS key pair for passworless authentication, addding it with ssh-add;
  
  * AWS PEM file
  
    PEM file downloaded from AWS and configured in ansible.cfg at private_key_file parameter.
    Generate the keipair in the AWS Web Console normally and download the pem file, placing it in keys directory

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
  
  `ansible-playbook ec2_setup_vpc.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC"`

* Create the subnets. Let's create the Public subnet that will have a jump box (bastion)

  `ansible-playbook ec2_setup_subnet.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC ec2_subnet=PublicSubnet"`

  Notice that it uses the network/PublicSubnet.yml, same deal as before. Vars will be replaced with most current content,
  but they must exist prior to calling the script

  This script also creates Internet Gateways and Routes if specified to be a public subnet

* Create the Security Group so you don't instantiate the EC2 into a default one

  This security group will allow us to SSH into it, run a yum update and etc...
  It will be restricted to my machine's IP only, which is collected real time during the SG creation.

  There is a script you can use later to update your IP across all your machines by role, for example: ec2_update_admin_ipaddr.yml

  `ansible-playbook ec2_create_sg.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC ec2_subnet=PublicSubnet ec2_sg=AdminSecurityGroup"`

* Create a NAT gateway

  This is useful for your instances behind a private network to have Internet access shielded by a NAT gateway so they can package installs and updates

 `ansible-playbook ec2_create_nat_gateway.yml -e "ec2_region=us-east-1 ec2_vpc=WordpressVPC ec2_private_subnet=PrivateSubnet ec2_public_subnet=PublicSubnet"`

 The NAT gateway is created in the public subnet and allocates an Elastic IP (EIP) for it. Then, in the private subnet, you create a default route for it to addresses outside your subnets. The Security Groups must allow this, keep that in mind...

### Instantiate the EC2

* Instantiate the Admin Jumpbox, a.k.a. bastion

  `ansible-playbook ec2_provision_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=AdminJumpbox ec2_subnet=PublicSubnet ec2_sg=AdminSecurityGroup"`

  The ec2_sg is optional, but since I want to demonstrate the SSH functionality, I'll put this instance in a security group that enables SSH access from my IP. Remember to setup they keypair PEM in AWS EC2.

* Updates the IP address of the bastion in the WPServer role configuration
 
  `ansible-playbook ec2_update_bastion.yml -e "ec2_region=us-east-1 ec2_role=WPServer ec2_bastion_role=AdminJumpbox"`


* Instantiate the WPServer, a.k.a. the web server

  `ansible-playbook ec2_provision_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=WPServer ec2_subnet=PrivateSubnet ec2_sg=WPServerSecurityGroup"`
  
* Perform a yum update on all instances of that same role

  Now we can install and update all Linux software in these instances at once by role

    * Updates:
  
    `ansible-playbook ec2_yum_update_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=AdminJumpbox"`
  
    `ansible-playbook ec2_yum_update_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=WPServer"`

    * Instalations:
  
    `ansible-playbook ec2_yum_install_packages_by_role.yml -e "ec2_region=us-east-1 ec2_role=WPServer"`

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
 `ansible-playbook ec2_delete_nat_gateway.yml -e "ec2_region=us-east-1 ec2_subnet=PublicSubnet"`

## TODO:
  - [x] Basic Instantiation scripts
  - [x] EC2 roles for a bastion and webserver
  - [x] VPC, subnet and NAT Gateway scripts
  - [x] Yum package scripts
  - [ ] ELB instantiation and configuration
  - [x] A script to remove NAT Gateways and free EIP
  - [ ] Wordpress installation script
  - [ ] RDS instantiation and configuration
