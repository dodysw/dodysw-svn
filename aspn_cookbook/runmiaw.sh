#!/bin/bash
python cookbookgenerator.py
tar -cjf ~/public_html/private/cookbook.tar.bz2 result/
touch ~/public_html/private/status.done