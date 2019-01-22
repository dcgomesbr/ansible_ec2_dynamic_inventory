# ansible_ec2_dynamic_inventory
Some experiments with Ansible, ec2.py, AWS EC2 and dynamic inventory techniques

Pre-requisites:
- AWS account capable of creating EC2 free tier eligible instances, RDS and CloudFront
- Python and Boto properly installed and configured for your AWS account
- Ansible
- OpenSSH / ssh agent

Purpose:
 Experiment with provisioning and maintaining EC2 fleets and services using solely Ansible and Boto, making use of dynamic machine inventories instead of a static hosts file.
