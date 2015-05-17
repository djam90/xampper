# Xampper
## Create a new Apache virtual host in seconds when using Xampp for Windows

### Installation
Install using composer globally using the following command:
`composer global require "djam90/xampper=1.0.1"`

### Usage
1- Ensure that your composer `bin` directory is in your PATH

2- Run from the command line: `xampper new`

### Assumptions
- You have the latest Xampp for Windows, with Apache 2.4
- Your hosts file is located at C:\Windows\System32\drivers\etc\hosts
- Your Apache virtual hosts file is located at C:\xampp\apache\conf\extra\httpd-vhosts.conf

### What Happens?
This package will add an extra virtual host at the bottom of your file, and an entry into the hosts file, something I have to do regularly when creating a new project every other day. 