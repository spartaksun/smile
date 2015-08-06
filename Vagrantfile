# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.box_url = "https://cloud-images.ubuntu.com/vagrant/trusty/current/trusty-server-cloudimg-amd64-vagrant-disk1.box"
  config.vm.host_name = "smile.localhost"
  config.vm.network "private_network", ip: "192.168.66.11"

  config.vm.network :forwarded_port,
      guest: 22,
      host: 2223,
      id: "ssh",
      auto_correct: true

  config.vm.synced_folder ".", "/var/www/smile", type: "nfs"

  config.vm.provider "virtualbox" do |v|
      v.name = "smile"
      v.memory = 4096
      v.cpus = 2
  end


  config.vm.provision :ansible do |a|
       a.playbook = "playbooks/provision.yml"
       a.inventory_path = "environments.ini"
       a.limit = "all"
  end
end