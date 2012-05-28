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
declare -a DIGITAL_IN=('' '' '' '' '' '' '' '');
declare -a ANALOG_IN=('' '' '' '' '' '' '' '');
digital_in_count=${#DIGITAL_IN[@]}
analog_in_count=${#ANALOG_IN[@]}
echo "Number of digital input channels: $digital_in_count"
echo "Number of analog input channels: $analog_in_count"
user_id=1

# ------------------------------------------------------------
# Do Something
# ------------------------------------------------------------
while true
do
  #clear

# Digital channels

index=0
while [ "$index" -lt "$digital_in_count" ]
do
  current=`mot_ctl i $index`
  previous=${DIGITAL_IN[$index]}
  if [ ! -z "$previous" -a "$current" != "$previous" ]
  then
    trigger_id=28
	if [ "$current" -gt 0 ]
    then
      trigger_id=27
    fi
	#fire external trigger to Cyclone PHP system
    echo "digital $index : $previous -> $current"
	#echo "curl -d \"user_id=${user_id}&trigger_id=${trigger_id}&channel=${index}&previous=${previous}&current=${current}\" http://localhost/cyclone/srv_ext_trigger.php"
	curl -d "user_id=${user_id}&trigger_id=${trigger_id}&channel=${index}&previous=${previous}&current=${current}" http://localhost/cyclone/srv_ext_trigger.php 1>/dev/null 2>&1 &
  fi
  DIGITAL_IN[$index]=$current
  ((index++))
done

# Analog channels

index=0
while [ "$index" -lt "$analog_in_count" ]
do
  current=`mot_ctl a $index | cut -d "x" -f2 | tr '[a-z]' '[A-Z]' | (read hex; echo $(( 0x${hex} )))`	#clean up raw value & convert to decimal
  previous=${ANALOG_IN[$index]}
  if [ ! -z "$previous" -a "$current" != "$previous" ]
  then
    diff=$(( $current - $previous ))
    diff=`echo ${diff#-}`
    if [ "$diff" -gt 5 ]		# 2% of 255
	then
	  #fire an external trigger to Cyclone PHP system
	  trigger_id=29
      echo "analog $index : $previous -> $current (diff: $diff)"
	  #echo "curl -d \"user_id=${user_id}&trigger_id=${trigger_id}&channel=${index}&previous=${previous}&current=${current}\" http://localhost/cyclone/srv_ext_trigger.php"
	  curl -d "user_id=${user_id}&trigger_id=${trigger_id}&channel=${index}&previous=${previous}&current=${current}" http://localhost/cyclone/srv_ext_trigger.php 1>/dev/null 2>&1 &
    fi
  fi
  ANALOG_IN[$index]=$current
  ((index++))
done

# End of while-loop
sleep 5
done

# ------------------------------------------------------------
# Cleanup lock file (optional)
# ------------------------------------------------------------
rm -f "${LCK_FILE}"
exit 0