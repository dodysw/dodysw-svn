from urlparse import urlparse
import socket, sys

urls = """
www.deakin.edu.au
www.canberra.edu.au
www.unsw.edu.au
www.uwa.edu.au
www.latrobe.edu.au
www.qut.edu.au
www.rmit.edu.au
www.swin.edu.au
www.unimelb.edu.au
www.cs.mu.oz.au
www.rwh.org.au
www.unsw.adfa.edu.au
www.uq.edu.au
www.usyd.edu.au
www.uws.edu.au
www.vu.edu.au
www.caul.edu.au
www.monash.edu.au
www.jcu.edu.au
www.mq.edu.au
www.jason.edu.au
www.cqu.edu.au
www.ballarat.edu.au
www.acid.net.au
www.cci.edu.au
www.smartinternet.com.au
www.csiro.au
www.ento.csiro.au
anic.ento.csiro.au
www.anwc.csiro.au
www.asris.csiro.au
www.atnf.csiro.au
genetech.csiro.au
sgrl.csiro.au
www.bbm.csiro.au
www.cazr.csiro.au
www.cip.csiro.au
www.cmis.csiro.au
www.cmit.csiro.au
www.cse.csiro.au
www.fungibank.csiro.au
www.det.csiro.au
www.em.csiro.au
www.ffp.csiro.au
www.foodscience.afisc.csiro.au
www.hpccc.gov.au
www.ict.csiro.au
www.landlinks.csiro.au
www.minerals.csiro.au
www.pi.csiro.au
www.publish.csiro.au
www.scienceimage.csiro.au
www.syd.dem.csiro.au
www.terc.csiro.au
www.tfrc.csiro.au
www.tft.csiro.au
www.bom.gov.au
primeministers.naa.gov.au
www.aac.adfc.gov.au
www.aao.gov.au
www.archivenet.gov.au
www.developmentgateway.com.au
www.nbcc.org.au
www.ovariancancerprogram.org.au
www.breasthealth.com.au
www.hyperhistory.org
www.cybersmartkids.com.au
www.exporthub.gov.au
www.foundingdocs.gov.au
www.naa.gov.au
www.fta.gov.au
www.peoplesvoice.gov.au
www.racismnoway.com.au
www.themara.com.au
www.nsw.gov.au
www.darlingharbour.com
www.aho.nsw.gov.au
www.legislation.nsw.gov.au
www.kids.nsw.gov.au
www.communitybuilders.nsw.gov.au
www.digitaltv.nsw.gov.au
www.de.com.au
www.daa.nsw.gov.au
www.dlg.nsw.gov.au
www.emergency.nsw.gov.au
www.gcio.nsw.gov.au
www.msmr.nsw.gov.au
www.nswis.com.au
www.parliament.nsw.gov.au
www.pco.nsw.gov.au
www.spinalinfo.nsw.gov.au
www.ses.nsw.gov.au
www.records.nsw.gov.au
www.sydneyathleticcentre.com.au
www.sca.nsw.gov.au
www.sch.edu.au
www.shfa.nsw.gov.au
www.sydneyvisitorcentre.com
www.therocks.com
www.visitnsw.com.au
www.ssroc.nsw.gov.au
www.hornsby.nsw.gov.au
www.greenwebsydney.net.au
www.museum.vic.gov.au
www.museumsaustralia.org.au
linux.anu.edu.au
mirror.aarnet.edu.au
mirrors.uwa.edu.au
www.au.netbsd.org
online.socialchange.net.au
tomen.elcom.com.au
www.elcom.com.au
www.bevilles.com.au
abn.agron.com
www.gilkon.com.au
www.lightcor.com.au
www.whiteknight.com.au
www.atp.com.au
www.readingroom.com.au
nicta.com.au
news.csu.edu.au
www.duralirrigation.com.au
www.redgumbooks.com
www.supamac.com
www.anmea.com
www.atp.com.au
www.mc2pacific.com
www.wroughtartworks.com.au
www.streamlinedmigration.com
www.smrs.com.au
www.brba.com.au
www.mavic.asn.au
cassowary.emuusers.org
www.foim.asn.au
group.cbn.org.au
136.154.202.91
www.vroom.org.au
"""

if __name__ == '__main__':
    original_ip_list = {}
    octet3_ip_list = {}
    # parse each line of url, convert into ip
    for url in urls.split('\n'):
        url = url.strip()
        if url == "": continue
        if 'http://' in url:
            address = urlparse(url)[1].split(':')[0]
        else:
            address = url.split(':')[0]
        try:
            ip_list = socket.gethostbyname_ex(address)[2]   # get all ip related to the same hostname, usually 1
            print >>sys.stderr, '%s => %s' % (address, ip_list)
        except socket.gaierror:
            # unresolved address
            continue

        # add into ip list, and create a new list that only contains the first 3 octet unique
        for ip in ip_list:
            original_ip_list[ip] = address
            key = ip[0:ip.rindex('.')]
            octet3_ip_list.setdefault(key, '')
            octet3_ip_list[key] += ' ' + address

    print >>sys.stderr, '%d urls compressed to %d addresses' % (len(original_ip_list), len(octet3_ip_list))
    # for each of that 3 octet IP, add range 1 to 254 as 4th octet, and if it's not in the first list, do a
    for octet3_ip, original_address in octet3_ip_list.items():
        # don't attempt ANU websites
        if octet3_ip.startswith('150.203'):
            continue
        print '<p>Original url <a href="http://%s">%s</a>:<ul>' % (original_address, original_address)
        print >>sys.stderr, 'Browsing %s...' % original_address
        for octet4 in range(1,255):
            ip = "%s.%s" % (octet3_ip , octet4)
            if ip in original_ip_list:
                continue

            # 1. reverse resolve to domain name
            ip_from_host = 'not attempted'
            try:
                hostname = socket.gethostbyaddr(ip)[0]
            except socket.herror:
                hostname = 'unknown'

            if hostname != 'unknown':
                # 2. resolve the domain name back to ip
                try:
                    ip_from_host = socket.gethostbyname(hostname)
                except socket.gaierror:
                    continue

            # 3. ping tcp connection on port 80 on that IP
            try:
                print >>sys.stderr, 'Attempting %s...' % ip,
                s = socket.socket(socket.AF_INET,socket.SOCK_STREAM)
                s.settimeout(4)
                s.connect((ip,80))
                s.close()
                print >>sys.stderr, 'OK'
                # 4. if found, print "<li><a href="http://original ip">original ip</a> - <a href="http://domain name">domain name</a> - <a href="resolve back ip">resolve back ip</a></li>"
                print '<li><a href="http://%s">%s</a> - <a href="http://%s">%s</a> - <a href="http://%s">%s</a></li>' % (ip, ip, hostname, hostname, ip_from_host, ip_from_host)
            except socket.error:
                print >>sys.stderr, 'fail'
                pass
        print '</ul>'
