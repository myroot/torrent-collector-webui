#!/usr/bin/python
# -*- coding: utf-8 -*-

import bencode
import MySQLdb
import dbpass
import cgi

db = None
def connectDB():
    global db
    if db :
        return db
    db = MySQLdb.connect('localhost', dbpass.id, dbpass.passwd, dbpass.dbname)
    db.query("set character_set_connection=utf8;")
    db.query("set character_set_server=utf8;")
    db.query("set character_set_client=utf8;")
    db.query("set character_set_results=utf8;")
    db.query("set character_set_database=utf8;")
    return db

def getTorrentFileInfo( fileno ) :
    db = connectDB()
    cur = db.cursor()
    cur.execute("select `data` from torrent where no = %s",(fileno,))
    torrent = cur.fetchone()[0]
    info = bencode.bdecode(torrent)

    result = [] 
    if info['info'].has_key('files') :
        for detail in info['info']['files']:
            unit = ['B','KB','MB','GB']
            length = detail['length'] / 1024.0
            depth = 0            
            while length > 1 :
                depth += 1
                detail['length'] = length
                length = detail['length'] / 1024.0
            readableSize = '%0.2f%s'%(detail['length'],unit[depth])            
            result.append({'name':detail['path'][-1],'length':readableSize})
    else:
        unit = ['B','KB','MB','GB']
        length = info['info']['length'] / 1024.0
        depth = 0
        while length > 1 :
            depth += 1
            info['info']['length'] = length
            length = info['info']['length'] / 1024.0
        readableSize = '%0.2f%s'%(info['info']['length'], unit[depth])
        result.append({'name':info['info']['name'],'length':readableSize})
    return result

if __name__ == '__main__':
    print "Content-Type: text/html\n";
    print '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1"></head><body>'
    form = cgi.FieldStorage()
    id = 0
    try:
        id = form['id'].value
    except:
        print 'error'

    if not id == 0 :    
        for x in getTorrentFileInfo(id):
            print '[%s] %s<br>'%(x['length'],x['name'])

    print '</body></html>'

