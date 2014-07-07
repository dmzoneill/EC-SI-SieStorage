#!/bin/bash

# force daily 
FORCE=0

# csv downloads
DUMPPATH="/srv/www/siestorage.ir.intel.com/stoddump"

# Day seconds
DAYSECS=86000

# last daily
LASTDAILY=`cat $DUMPPATH/daily`

# today
DUMPDATE=`date +%s`

# new daily
NEWDAILY=$((DUMPDATE-LASTDAILY))

# sites
SITES=`/usr/intel/bin/stodstatus storage-users --target start.ir.intel.com --sort-by Usage --format csv --fields all | awk -F "," '{print $8}' | sort | uniq`

for SITE in $SITES
do

	if [ "${SITE}" == "Cell" ]; then
		continue;
	fi

	if [[ ! -e $DUMPPATH/$SITE ]]; then
    		mkdir $DUMPPATH/$SITE
	fi

	DUMPPATHSITE="/srv/www/siestorage.ir.intel.com/stoddump/${SITE}"
	
	/usr/intel/bin/stodstatus allocation --target start.ir.intel.com --format csv --fields all > $DUMPPATHSITE/allocation.csv
	/usr/intel/bin/stodstatus storage-users --target start.ir.intel.com --sort-by Usage --format csv --fields all "cell='${SITE}'" > $DUMPPATHSITE/users.csv
	/usr/intel/bin/stodstatus areas --target start.ir.intel.com --format csv --fields all "cell='${SITE}'" > $DUMPPATHSITE/areas.csv
	/usr/intel/bin/stodstatus disks --target start.ir.intel.com --format csv --fields all "cell='${SITE}'" > $DUMPPATHSITE/disks.csv
	/usr/intel/bin/stodstatus fileservers --target start.ir.intel.com --format csv --fields all "cell='${SITE}'" > $DUMPPATHSITE/fileservers.csv
	/usr/intel/bin/stodstatus areas --target start.ir.intel.com --format csv --sort-by Usage --fields BusinessGroup "cell='${SITE}'" > $DUMPPATHSITE/businessgroups.csv
	/usr/intel/bin/stodstatus areas --target start.ir.intel.com --format csv --sort-by Usage --fields Owner "cell='${SITE}'" > $DUMPPATHSITE/owners.csv
	/usr/intel/bin/stodstatus areas --target start.ir.intel.com --format csv --sort-by Usage --fields Project "cell='${SITE}'" > $DUMPPATHSITE/projects.csv

	if [ "$NEWDAILY" -ge "$DAYSECS" ] || [ "$FORCE" -eq "1" ]; then
		echo "$DUMPDATE" > $DUMPPATH/daily	
		mkdir -p $DUMPPATHSITE/trending/.
		cp $DUMPPATHSITE/allocation.csv $DUMPPATHSITE/trending/allocation-$DUMPDATE.csv
		cp $DUMPPATHSITE/users.csv $DUMPPATHSITE/trending/users-$DUMPDATE.csv
		cp $DUMPPATHSITE/areas.csv $DUMPPATHSITE/trending/areas-$DUMPDATE.csv
		cp $DUMPPATHSITE/disks.csv $DUMPPATHSITE/trending/disks-$DUMPDATE.csv
		cp $DUMPPATHSITE/fileservers.csv $DUMPPATHSITE/trending/fileservers-$DUMPDATE.csv
		cp $DUMPPATHSITE/businessgroups.csv $DUMPPATHSITE/trending/businessgroups-$DUMPDATE.csv
		cp $DUMPPATHSITE/owners.csv $DUMPPATHSITE/trending/owners-$DUMPDATE.csv
		cp $DUMPPATHSITE/projects.csv $DUMPPATHSITE/trending/projects-$DUMPDATE.csv
	fi
done

echo "lastrun" > $DUMPPATH/lastrun
date +"%d-%b-%Y %T" >> $DUMPPATH/lastrun
chown -R wwwrun:www $DUMPPATH/*
