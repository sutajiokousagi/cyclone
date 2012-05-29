#!/bin/bash

mysqldump -uroot -pcyclone --add-drop-database --add-drop-table --skip-add-locks --skip-comments --databases cyclone > cyclone.sql
