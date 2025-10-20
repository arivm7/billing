#!/bin/bash



grep -L "Project : s1.ri.net.ua" -R app/       | grep '.php'
grep -L "Project : s1.ri.net.ua" -R billing/   | grep '.php'
grep -L "Project : s1.ri.net.ua" -R config/    | grep '.php'
grep -L "Project : s1.ri.net.ua" -R install/   | grep '.php'
grep -L "Project : s1.ri.net.ua" -R nbproject/ | grep '.php'
grep -L "Project : s1.ri.net.ua" -R public/    | grep '.php'
grep -L "Project : s1.ri.net.ua" -R storage/   | grep '.php'
