#!/usr/bin/python

import os

#trouvons la mesure principale

a=int(input('NUMERATEUR=')) 
b=int(input('DENOMINATEUR=')) 

if b<0 :
    a=-a
    b=-b

if ((a/b)<0) : 
	while ((a/b)<-1): 
		a = a + 2*b 
elif ((a/b)>0) : 
	while ((a/b)>1): 
		a = a - 2*b
t1 = a
t2 = b
a = abs(a)
b = abs(b)

while a*b !=0 :
        if a > b :
                a = a-b
        else :
                b = b-a

if a==0 :
    pgcd = b
    print ("pgcd=",pgcd)
    t4 = a
    a = t1 / b
    b = t2 / b
    
elif b==0:
    pgcd = a
    print ("pgcd=",pgcd)
    t3 = a
    a = t1 / a
    b = t2 / t3

print ("a=",a,"b=",b)

os.system("pause")
