#!/bin/bash

ansible-playbook rds_get_facts.yml -e "ec2_region=us-east-1 rds_role=WordPressDB"

ansible-playbook ec2_lb_delete_by_role.yml -e "ec2_region=us-east-1 ec2_role=WPServer"

ansible-playbook ec2_term_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=AdminJumpbox"

ansible-playbook ec2_term_by_region_role.yml -e "ec2_region=us-east-1 ec2_role=WPServer"

ansible-playbook vpc_delete_nat_gateway.yml -e "ec2_region=us-east-1 ec2_subnet=PublicSubnetBastion"
