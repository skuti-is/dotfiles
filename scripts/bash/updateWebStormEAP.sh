#!/usr/bin/env bash

# Directory where you want WebStorm EAP to be installed
INSTALL_DIR="/opt/webstorm"

echo "Checking for updates..."

BUILD_FILE="${INSTALL_DIR}/build.txt"
FILE=$(curl -s https://confluence.jetbrains.com/display/WI/WebStorm+EAP | grep -Eo "WebStorm-EAP-[0-9\.]+(-custom-jdk-linux)?.tar.gz" | head -1)
VERSION=$(echo $FILE | grep -Eo "([0-9]+\.[0-9]+(\.[0-9]+)?+)")
URL="http://download-cf.jetbrains.com/webstorm/${FILE}"

if [ ! $FILE ]
then	
	echo "URL does not exist: $URL"
	exit
fi

# Check if file exists
HEADER=$(curl $URL --head --silent)

# Check current installed version if available
if [ -f "${BUILD_FILE}" ]
then
  CURRENT_VERSION=$(cat ${INSTALL_DIR}/build.txt | grep -Eo "([0-9]+\.[0-9]+)")
  echo "Found WebStorm-EAP-${CURRENT_VERSION} at ${INSTALL_DIR}"
  if [ "${CURRENT_VERSION}" == "${VERSION}" ]
  then
    echo "You have the latest version of WebStorm EAP already installed."
    exit
  fi
else
  echo "No WebStorm-EAP found at ${INSTALL_DIR}"
fi

echo "Updating to WebStorm-EAP-${VERSION}"

TMP_DIR="/tmp/WebStorm-EAP-${VERSION}"
TMP_FILE="${TMP_DIR}/${FILE}"
EXTRACTED="${TMP_DIR}/WebStorm-${VERSION}"

# Create tmp dirctory for download and extracting
if [ ! -d "${TMP_DIR}" ]
then
  mkdir "${TMP_DIR}"
fi

# Check if already extracted
if [ ! -d "${EXTRACTED}" ]
then
  # Check if already downloaded
  if [ ! -f "${TMP_FILE}" ]
  then
    echo "Downloading to ${TMP_FILE}"
    curl --progress-bar -o "${TMP_FILE}" "${URL}"
  fi

  echo "Extracting..."
  tar zxf ${TMP_FILE} -C ${TMP_DIR}
fi

echo "Installing to ${INSTALL_DIR}"
if [ ! -d "${INSTALL_DIR}" ]
then
  mkdir -p "${INSTALL_DIR}"
fi

rm -rf ${INSTALL_DIR}
mv "${EXTRACTED}" "${INSTALL_DIR}/"

echo "Cleanup..."
rm -rf "${TMP_DIR}"

echo "Done."
