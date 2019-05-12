#!/bin/bash


echo "# <center><big>Ecclesia**CRM** documentation de l'API</big></center>
----

Ecclesia**CRM** utilise Slim 3.9.2 qui permet d'accéder de manière restreinte aux éléments du CRM.


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




rm output_file.md