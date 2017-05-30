#!/usr/bin/env python3
# @author NetBUG aka Oleg Urzhumtcev
# @description Automating requests to Skuuper CLeaner API

import requests
import os
import re
from termcolor import colored, cprint
from urllib.parse import urlparse

backend_url = 'http://localhost:7099/tmx/process'

def align(fp, fp2, ldc=False, lf=False, aligner='champollion', df='rcdict.utf8.txt'):
    fn = os.path.basename(fp)
    fn2 = os.path.basename(fp2)
    files = {'source_text': (fn, open(fp, 'rb')), 'destination_text': (fn2, open(fp2, 'rb'))}
    payload = {'source_language': 'ru', 
               'destination_language': 'zh', 
               'aligner': 'aligner-ch' if aligner == 'champollion' else 'aligner',
               'dict': df,
               'use_ldc_chunker': 'on' if ldc == True else 'off',
               'use_lf_aligner': 'on' if lf == True else 'off'}
    print('Aligning %s and %s with %s (%s), LDC %s, LF stack %s' % (colored(fn, 'white', attrs=['bold']), colored(fn2, 'white', attrs=['bold']), colored(aligner, 'cyan'), colored(df, 'cyan', attrs=['bold']), colored(payload['use_ldc_chunker'], 'green' if payload['use_ldc_chunker'] == 'on' else 'red', attrs=['bold']), colored(payload['use_lf_aligner'], 'green' if payload['use_lf_aligner'] == 'on' else 'red', attrs=['bold'])))
    r = requests.post(backend_url, files=files, data=payload)
    tmxlink = 'Error getting '
    res = re.search(r'href=[\'"]?(/tmx/download[^\'" >]+)', r.text)
    if res:
        tmxlink = res.group(0)[6:]
    o = urlparse(backend_url)
    r1 = requests.get(o.scheme + "://" + o.netloc + tmxlink)
    if not tmxlink[0] == 'E':
        cnt = 0
        fp_out = tmxlink[14:] + ".tmx"          # или сделай сам, чтобы по параметрам имя собиралось, мне лень
        while os.path.isfile(fp_out):
            fp_out = tmxlink[14:] + '_' + str(cnt) + ".tmx"
            cnt += 1
        print("Saving: " + fp_out)
        with open(fp_out, 'w') as fo:
            fo.write(r1.text)
        return fp_out
    else: 
        print("Error getting TMX!")
        return ""


if __name__ == '__main__':
    align('thirdparty/aligner_ch/demo/CPP20000210000021.r.st', 'thirdparty/aligner_ch/demo/CPP20000210000021.c.utf8.st', ldc=True, lf=False, aligner='hunalign', df='ru-zh.dic')
    align('thirdparty/aligner_ch/demo/CPP20000210000021.r.st', 'thirdparty/aligner_ch/demo/CPP20000210000021.c.utf8.st', ldc=False, lf=False, aligner='hunalign', df='ru-zh.dic')
