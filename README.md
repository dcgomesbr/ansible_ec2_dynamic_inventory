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
