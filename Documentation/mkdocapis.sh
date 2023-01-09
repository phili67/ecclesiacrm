#!/bin/bash


echo "# <center><big>Ecclesia**CRM** documentation de l'API</big></center>
----

Ecclesia**CRM** use Slim 4.10.0 which allow to make api call to the restricted area of the CRM.


" > "api.md"

# events
php slim-doc.php -i ../src/api/routes/calendar/calendar-calendarV2.php -o output_file.md -n"calendar"
cat output_file.md >> api.md
php slim-doc.php -i ../src/api/routes/calendar/calendar-eventsV2.php -o output_file.md -n"events"
cat output_file.md >> api.md

# cart
php slim-doc.php -i ../src/api/routes/cart.php -o output_file.md -n"Cart"
cat output_file.md >> api.md

# documents
php slim-doc.php -i ../src/api/routes/documents/documents-ckeditor.php -o output_file.md -n"ckeditor"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/documents/documents-document.php -o output_file.md -n"document"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/documents/documents-filemanager.php -o output_file.md -n"filemanager"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/documents/documents-sharedocument.php -o output_file.md -n"sharedocument"
cat output_file.md >> api.md

#finances
php slim-doc.php -i ../src/api/routes/finance/finance-deposits.php -o output_file.md -n"deposits"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/finance/finance-donationfunds.php -o output_file.md -n"donationfunds"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/finance/finance-payments.php -o output_file.md -n"payments"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/finance/finance-pledges.php -o output_file.md -n"pledges"
cat output_file.md >> api.md

# fundraiser
php slim-doc.php -i ../src/api/routes/fundraiser/fundraiser.php -o output_file.md -n"fundraiser"
cat output_file.md >> api.md

#geocoder
php slim-doc.php -i ../src/api/routes/geocoder.php -o output_file.md -n"geocoder"
cat output_file.md >> api.md

# kiosk
php slim-doc.php -i ../src/api/routes/kiosks.php -o output_file.md -n"kiosks"
cat output_file.md >> api.md

#mailchimp
php slim-doc.php -i ../src/api/routes/mailchimp.php -o output_file.md -n"mailchimp"
cat output_file.md >> api.md

#people
php slim-doc.php -i ../src/api/routes/people/people.php -o output_file.md -n"people"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/people/people-attendees.php -o output_file.md -n"attendees"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/people/people-families.php -o output_file.md -n"families"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/people/people-groups.php -o output_file.md -n"groups"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/people/people-persons.php -o output_file.md -n"persons"
cat output_file.md >> api.md

# public
php slim-doc.php -i ../src/api/routes/public/public-data.php  -o output_file.md -n"data"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/public/public-register.php  -o output_file.md -n"register"
cat output_file.md >> api.md

#search nothing
php slim-doc.php -i ../src/api/routes/search.php -o output_file.md -n"search"
cat output_file.md >> api.md

#sidebar
php slim-doc.php -i ../src/api/routes/sidebar/sidebar-mapicons.php -o output_file.md -n"mapicons"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/sidebar/sidebar-menulinks.php -o output_file.md -n"menulinks"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/sidebar/sidebar-properties.php -o output_file.md -n"properties"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/sidebar/sidebar-roles.php -o output_file.md -n"roles"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/sidebar/sidebar-volunteeropportunity.php -o output_file.md -n"volunteeropportunity"
cat output_file.md >> api.md

# pastoral care
php slim-doc.php -i ../src/api/routes/pastoralcare/pastoralcare.php -o output_file.md -n"pastoralcare"
cat output_file.md >> api.md

#sundayschool
php slim-doc.php -i ../src/api/routes/sundayschool.php -o output_file.md -n"sundayschool"
cat output_file.md >> api.md

#system
php slim-doc.php -i ../src/api/routes/system/system.php -o output_file.md -n"system"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/system/system-custom-fields.php -o output_file.md -n"custom-fields"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/system/system-synchronize.php -o output_file.md -n"synchronize"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/system/system-database.php -o output_file.md -n"database"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/system/system-gdrp.php -o output_file.md -n"gdrp"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/system/system-issues.php -o output_file.md -n"issues"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/system/system-system-upgrade.php -o output_file.md -n"systemupgrade"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/system/system-timerjobs.php -o output_file.md -n"timerjobs"
cat output_file.md >> api.md

#user
php slim-doc.php -i ../src/api/routes/user/user-role.php -o output_file.md -n"userrole"
cat output_file.md >> api.md

php slim-doc.php -i ../src/api/routes/user/user-users.php -o output_file.md -n"users"
cat output_file.md >> api.md

#
# Plugins
#

# meeting plugin
php slim-doc.php -i ../src/Plugins/MeetingJitsi/api/plgnapi.php -o output_file.md -n"meeting (plugin)"
cat output_file.md >> api.md


# now we copy the file at the right place
cp api.md doc-fr/docs/user-guide/doc-dev/doc-api/
cp api.md doc-en/docs/user-guide/doc-dev/doc-api/

rm output_file.md
rm api.md
