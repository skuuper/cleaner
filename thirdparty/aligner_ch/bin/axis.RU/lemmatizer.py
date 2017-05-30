#!/usr/bin/env python3
#coding=utf-8
# @author Maratych
# @description Lemmatizing Russian in clear cases (single analysis)

import pymorphy2
import sys
morph = pymorphy2.MorphAnalyzer()
from nltk import word_tokenize

line = 'Я люблю китайско-русский параллельный корпус НКРЯ!'

def normal_form(line):
    tokens = word_tokenize(line)
    lemmas = []
    for token in tokens:
        token = token.lower().strip()
        stems = [p.normal_form for p in morph.parse(token)]
        if len(set(stems)) == 1:
            lemmas.append(stems[0])
        else:
            #print ('non-unique word ' + token + ": " + repr(set(stems)))
            #print(repr(morph.parse(token)))
            lemmas.append(token)
    new_line = (' ').join(lemmas)
    return new_line

if __name__ == '__main__':
    print(normal_form(line))

