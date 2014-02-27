import pyrax
import sys

#credentials
pyrax.set_setting("identity_type", "rackspace")
pyrax.set_credential_file(".rackspace_cloud_credentials")
#services 
cdb = pyrax.cloud_databases
cdb = pyrax.connect_to_cloud_databases(region="IAD")

instances = cdb.list()
if not instances:
    print "There are no cloud database instances."
    print "Please create one and re-run this script."
    sys.exit()

print
print "Available Instances:"
for pos, inst in enumerate(instances):
    print "%s: %s (%s, RAM=%s, volume=%s) Status=%s" % (pos, inst.name,
            inst.flavor.name, inst.flavor.ram, inst.volume.size, inst.status)
