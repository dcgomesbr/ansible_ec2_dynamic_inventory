#!/bin/bash

ansible-playbook ec2_setup_vpc.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC"

ansible-playbook ec2_setup_subnet.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC ec2_subnet=PublicSubnetBastion"

ansible-playbook ec2_setup_subnet.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC ec2_subnet=PrivateSubnetWP01"

ansible-playbook ec2_create_sg.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC ec2_subnet=PublicSubnet ec2_sg=AdminSecurityGroup"

ansible-playbook ec2_create_sg.yml -e "ec2_region=us-east-1 ec2_network=WordpressVPC ec2_subnet=PublicSubnet ec2_sg=WPServerSecurityGroup"

ansible-playbook ec2_create_nat_gateway.yml -e "ec2_region=us-east-1 ec2_vpc=WordpressVPC ec2_private_subnet=PrivateSubnetWP01 ec2_public_subnet=PublicSubnetBastion"

ansible-playbook ec2_provision_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=AdminJumpbox ec2_subnet=PublicSubnetBastion ec2_sg=AdminSecurityGroup"

ansible-playbook ec2_update_bastion.yml -e "ec2_region=us-east-1 ec2_role=WPServer ec2_bastion_role=AdminJumpbox"

ansible-playbook ec2_provision_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=WPServer ec2_subnet=PrivateSubnetBastion ec2_sg=WPServerSecurityGroup"

ansible-playbook ec2_yum_update_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=AdminJumpbox"

ansible-playbook ec2_yum_update_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=WPServer"

ansible-playbook ec2_yum_install_packages_by_role.yml -e "ec2_region=us-east-1 ec2_role=WPServer"
