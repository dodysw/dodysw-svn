#!/bin/bash
rm -rf cache/
rm -rf result/
python cookbookgenerator.py
tar -cjf ~/public_html/private/cookbook.tar.bz2 result/
touch ~/public_html/private/status.done