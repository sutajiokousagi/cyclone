#!/bin/bash
# ------------------------------------------------------------
# Author      : Torin Nguyen
# Description : Daemon for NeTV hardware input/triggers
# ------------------------------------------------------------

# ------------------------------------------------------------
# Setup environment
# ------------------------------------------------------------
PDIR=${0%`basename $0`}
LCK_FILE=/tmp/`basename $0`.lck

# ------------------------------------------------------------
# Check if this script is already running
# ------------------------------------------------------------
if [ -f "${LCK_FILE}" ]; then

  # The file exists so read the PID
  # to see if it is still running
  MYPID=`head -n 1 "${LCK_FILE}"`

  TEST_RUNNING=`ps -p ${MYPID} | grep ${MYPID}`

  if [ -z "${TEST_RUNNING}" ]; then
    # The process is not running
    # Echo current PID into lock file
    echo $$ > "${LCK_FILE}"
  else
    echo "`basename $0` is already running [PID ${MYPID}]. Abort."
    exit 0
  fi

else
  echo "Not running"
  echo $$ > "${LCK_FILE}"
fi

# ------------------------------------------------------------
# Setup NeTV motor controller firmware
# ------------------------------------------------------------

firmware=`mot_ctl V`
echo "Firmware #${firmware}"
while [ "${#firmware}" -gt 10 ]
do
  firmware_setup=`/etc/init.d/netv_service motor`
  sleep 2
  if [ "${#firmware_setup}" -gt 300 ]		#if success, it will only have 268 characters
  then
    sleep 8
  fi
  firmware=`mot_ctl V`
done

# ------------------------------------------------------------
# Setup NeTV variables
# ------------------------------------------------------------
previous_digital_in=''
previous_analog_in=''
user_id=1
delay=1

# ------------------------------------------------------------
# Do Something
# ------------------------------------------------------------
while true
do

sleep ${delay}
delay=1

# Digital channels
# More efficient to send all digital input values & let PHP module split it into more events

#Eg: 0x20: 02
current=`mot_ctl i | cut -d " " -f2 | (read hex; echo $(( 0x${hex} )))`
if [ ! -z "$previous_digital_in" -a "$current" != "$previous_digital_in" ]
then
  trigger_id=29
  #fire external trigger to Cyclone PHP system
  echo "digital change : $previous_digital_in -> $current"
  #echo "curl -d \"user_id=${user_id}&trigger_id=${trigger_id}&previous=${previous_digital_in}&current=${current}\" http://localhost/cyclone/srv_ext_trigger.php"
  curl -d "user_id=${user_id}&trigger_id=${trigger_id}&previous=${previous_digital_in}&current=${current}" http://localhost/cyclone/srv_ext_trigger.php 1>/dev/null 2>&1 &
  ((delay++))
fi
previous_digital_in=${current}



# Analog channels
# More efficient to send all analog input values & let PHP module split it into more events

#no change in all analog channels
current=`mot_ctl a | sed 's/ *$//g' | sed "s/0x//g" | sed "s/ /-/g"`
if [ -z "$previous_analog_in" -o "$current" == "$previous_analog_in" ]
then
	previous_analog_in=${current}
 	continue;
fi

#echo "analog change: $previous_analog_in -> $current"
trigger_id=30
previous_array=(`echo $previous_analog_in | tr "-" "\n"`)
current_array=(`echo $current | tr "-" "\n"`)
previous_analog_in=${current}

#detect amount of change in each channel
index=0
hasBigChange=0
for temp_current in "${current_array[@]}"
do
	temp_current=`echo ${temp_current} | (read hex; echo $(( 0x${hex} )))`
	temp_previous=${previous_array[$index]}
	temp_previous=`echo ${temp_previous} | (read hex; echo $(( 0x${hex} )))`
	((index++))
		
	diff=$(( $temp_current - $temp_previous ))
    diff=`echo ${diff#-}`		#absolute value

    if [ "$diff" -lt 25 ]		#10% of 255
 	then
		continue;
	fi
	
	hasBigChange=1
	echo "analog ch #$(($index-1)): $temp_previous -> $temp_current [$diff]"
	break;
done

#no big change in all analog channels
if [ "$hasBigChange" -lt 1 ]
then
 	continue;
fi

#echo "curl -d \"user_id=${user_id}&trigger_id=${trigger_id}&previous=${previous_analog_in}&current=${current}" http://localhost/cyclone/srv_ext_trigger.php"
curl -d "user_id=${user_id}&trigger_id=${trigger_id}&previous=${previous_analog_in}&current=${current}" http://localhost/cyclone/srv_ext_trigger.php 1>/dev/null 2>&1 &
((delay+=2))

# End of while-loop
done

# ------------------------------------------------------------
# Cleanup lock file (optional)
# ------------------------------------------------------------
rm -f "${LCK_FILE}"
exit 0