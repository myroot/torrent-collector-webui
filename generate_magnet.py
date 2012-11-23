#!/usr/bin/python
# -*- coding: utf-8 -*-

import bencode
import MySQLdb
import dbpass
import cgi
import hashlib
import urllib

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

def getTorrentMagnetInfo( fileno ) :
    db = connectDB()
    cur = db.cursor()
    cur.execute("select `data` from torrent where no = %s",(fileno,))
    torrent = cur.fetchone()[0]
    metainfo = bencode.bdecode(torrent)
    info = metainfo['info']
    temp = hashlib.sha1(bencode.bencode(info))
    extra_topic  =  temp.hexdigest()
    display_name = urllib.quote(metainfo['info']['name'])
    tracker = urllib.quote(metainfo['announce'])
    result = 'magnet:?xt=urn:btih:%s&amp;dn=%s&amp;tr=%s' % (extra_topic, display_name, tracker)
    return result

if __name__ == '__main__':
    print "Content-Type: text/html\n";
    print '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>'
    form = cgi.FieldStorage()
    id = 0
    try:
        id = form['id'].value
    except:
        print 'error'

    if not id == 0 :    
        link = getTorrentMagnetInfo(id)
        print '<script> location.href=\'%s\'</script>'%link
        print '<a href=\'%s\'>%s</a>'%(link,link)
    print '</body></html>'

