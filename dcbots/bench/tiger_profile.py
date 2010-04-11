#~ import psyco
#~ psyco.profile()
import hotshot, hotshot.stats
import tiger


if __name__=='__main__':
    prof = hotshot.Profile("stones.prof")
    prof.runcall(tiger.test_speed)
    prof.close()
    stats = hotshot.stats.load("stones.prof")
    stats.strip_dirs()
    stats.sort_stats('time', 'calls')
    stats.print_stats(20)
