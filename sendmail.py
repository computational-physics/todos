# this file should have no root permission, in /var/www/mail_verify/sendmail.py
# sendmail.py -u username -f filepath
# file should be like {"title":"Horrible Particle Diffusion Debug:-(","date":"2014-09-03","completed":false,"important":false,"finish_date":"2014-09-03"}
import smtplib
import sys
import getopt
import json


def send(username, filepath):
    mail = ""
    f = open("/etc/usermail.list")
    lines = f.readlines()
    for l in lines:
        if not not l.strip() and l[0] != '#' and len(l.split()) >= 2:
            if l.split()[0] == username:
                mail = l.split()[1]

    f = open(filepath)
    lines = f.readlines()

    todo = json.loads(lines[0])
    dueOn = ''
    if 'due' in todo.keys() and str(todo['due']) != "":
        dueOn = todo['due']
    else:
        dueOn = 'not specify'

    SUBJECT = "[Group TODOS]Your Task: %s" % (todo['title'].encode('ascii', 'ignore').decode('ascii'))
    TEXT = """\
Dear %s,

Here is the notification of your task in https://ourphysics.org/apps/todos
---
Title: %s
Created on: %s
Due on: %s

Yours,
TODO robot
""" % (username, todo['title'].encode('ascii', 'ignore').decode('ascii'), todo['date'], dueOn)
    FROM = 'no-reply@ourphysics.org'
    TO = [mail]
    message = """\
From: %s
To: %s
Subject: %s

%s
""" % (FROM, ", ".join(TO), SUBJECT, TEXT)
    server = smtplib.SMTP('localhost')
    server.sendmail(FROM, TO, message)
    server.quit()
    print(username)


def main(argv):
    u = ""
    f = ""
    try:
        opts, args = getopt.getopt(argv, "u:f:")
    except getopt.GetoptError:
        print(-1)
        sys.exit(2)
    for opt, arg in opts:
        if opt == '-u':
            u = arg
        elif opt == '-f':
            f = arg
    send(u, f)


if __name__ == "__main__":
    main(sys.argv[1:])
