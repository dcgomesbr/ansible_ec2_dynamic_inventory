# ansible_ec2_dynamic_inventory
Some experiments with Ansible, ec2.py, AWS EC2 and dynamic inventory techniques

Pre-requisites:
* AWS Stuff
  - AWS account capable of creating EC2 free tier eligible instances, RDS and CloudFront
  - AWS CLI installed and configured with a working aws_access_key_id and aws_secret_access_key
  I'm NOT using ansible-vault here, keys will come from ~/.aws/credentials

* Python stuff
  - Python and modules
    Ansible
    Boto
     
    In inventory/base file, I setup ansible_python_interpreter parameter poiting to where my
    python binary is. This can be necessary if you have multiple Python instalations conflicting
    your OS (MacOS wasn't nice about it).

* SSH stuff
  - OpenSSH
    ssh-agent configured with the AWS key pair for passworless authentication
    (I call it keypair.pem) and it is configured in ansible.cfg at private_key_file
    Generate the keipair in the AWS Web Console normally and download the pem file.

* ec2.py scripts
  - Although it's in my repo's inventory directory, you can download the latest from:

  https://raw.githubusercontent.com/ansible/ansible/devel/contrib/inventory/ec2.ini
  https://raw.githubusercontent.com/ansible/ansible/devel/contrib/inventory/ec2.py
  
  They must go inside inventory directory, because in ansible.cfg it points inventory = inventory/
  
All the stuff above and how to make it work is broadly covered so just Google for it.

Purpose:

  Experiment with provisioning and maintaining EC2 fleets and services using solely Ansible and Boto, making use of dynamic machine inventories instead of a static hosts file.


How to use:
* Create the VPC for your app - let's say you want to do a Wordpress installation

  ansible-playbook ec2_setup_vpc.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC"

  Notice that it makes uses of networks/WordpressVPC.yml config file for the vars

  If ec2_vpc_id variable might be overwritten, but it must exist in the file
  You can set it up as undefined if you wish.
  ec2_vpc_id: undefined

* Create the subnets. Let's create the Public subnet that will have a jump box

  ansible-playbook ec2_setup_subnet.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC ec2_subnet=PublicSubnet"

  Notice that it uses the network/PublicSubnet.yml, same deal as before. Vars will be replaced with most current content, but must exist prior to calling the script

  This script also creates Internet Gateways and Routes if specified to be a public subnet

* Create the Security Group so you don't instantiate the EC2 into a default one

  This security group will allow us to SSH into it, run a yum update and etc...
  It will be restricted to my machine's IP only, which is collected real time during the SG creation.

  There is a script you can use later to update your IP across all your machines by role, for example: ec2_update_admin_ipaddr.yml

  ansible-playbook ec2_create_sg.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC ec2_subnet=PublicSubnet ec2_sg=AdminSecurityGroup"

* Instantiate the EC2

  ansible-playbook ec2_provision_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=AdminJumpbox ec2_sg=AdminSecurityGroup ec2_subnet=PublicSubnet"

  The ec2_sg is optional, but since I want to demonstrate the SSH functionality, I'll put this instance in a security group that enables SSH access from my IP. Remember to setup they keypair.pem for your AWS account.

* Perform a yum update on all instances of that same role

  Now we can update all Linux software in these instances at once by role

  ansible-playbook ec2_yum_update_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=AdminJumpbox"

* List all my instances' metadata

  ansible-playbook ec2_list_metadata.yml 

* Terminate all instances by role

  Let's terminate this instance so we don't risk paying AWS money overnight

  ansible-playbook ec2_term_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=AdminJumpbox"


TODO:

  A script to remove VPCs
  A role for a web server
  A security group for a web server 
  Wordpress installation script
  Database instantiation
