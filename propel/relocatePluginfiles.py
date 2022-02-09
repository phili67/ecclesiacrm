"""
    Copyright : Philippe Logel 2022
    This command is usefull for propel external schemas, to relocate the model in the plugin directory
"""

import xml.etree.ElementTree as ET
import glob, os
from pathlib import Path
import shutil

path = str(Path().resolve()).replace("propel","")

orig_path = path + "src/EcclesiaCRM/model/PluginStore/"
dest_path = path + "src/Plugins/"

print(path)

for file in glob.glob("*.xml"):
    if (file != "main.schema.xml"):
        root = ET.parse(file).getroot()

        # attention : <database package="MeetingJitsi" name="pluginstore" nameSpace="PluginStore">
        # package Name must feet to the Folder Name of the Plugin
        PluginName = root.attrib['package']
        real_dest_path = path + "src/Plugins/" + PluginName + "/core/"

        print(real_dest_path)

        for elem in root.findall('table'):
            class_name = elem.attrib['phpName']

            # in case the plugin model path is yet filled : only the original is deleted
            if os.path.isfile(real_dest_path + "model/" + class_name + ".php"):
                os.remove(orig_path + class_name + ".php")
            else:
                Path(orig_path + class_name + ".php").rename(real_dest_path + "model/" + class_name + ".php")

            if (os.path.isfile(real_dest_path + "model/" + class_name + "Query.php")):
                os.remove(orig_path + class_name + "Query.php")
            else:
                Path(orig_path + class_name + "Query.php").rename(real_dest_path + "model/" + class_name + "Query.php")

            # we have to delete all the Base and Map files
            if os.path.isfile(real_dest_path + "model/Base/" + class_name + ".php"):
                os.remove(real_dest_path + "model/Base/" + class_name + ".php")

            if os.path.isfile(real_dest_path + "model/Base/" + class_name + "Query.php"):
                os.remove(real_dest_path + "model/Base/" + class_name + "Query.php")

            Path(orig_path + "Base/" + class_name + ".php").rename(real_dest_path + "model/Base/" + class_name + ".php")
            Path(orig_path + "Base/" + class_name + "Query.php").rename(real_dest_path + "model/Base/" + class_name + "Query.php")

            if os.path.isfile(real_dest_path + "model/Map/" + class_name + "TableMap.php"):
                os.remove(real_dest_path + "model/Map/" + class_name + "TableMap.php")

            Path(orig_path + "Map/" + class_name + "TableMap.php").rename(real_dest_path + "model/Map/" + class_name + "TableMap.php")

# now we delete the PluginStore directory, it's right now unusefull
shutil.rmtree(orig_path)
