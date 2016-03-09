#!/usr/bin/env bash

# Directory where you want PhpStorm EAP to be installed
INSTALL_DIR="/opt/phpstorm"

echo "Checking for updates..."

BUILD_FILE="${INSTALL_DIR}/build.txt"
FILE=$(curl -s https://confluence.jetbrains.com/display/PhpStorm/PhpStorm+Early+Access+Program | grep -Eo "PhpStorm-EAP-[0-9\.]+(-custom-jdk-linux)?.tar.gz" | head -1)
VERSION=$(echo $FILE | grep -Eo "([0-9]+\.[0-9]+(\.[0-9]+)?+)")
URL="http://download-cf.jetbrains.com/webide/${FILE}"

echo $FILE
echo $VERSION

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
  echo "Found PhpStorm-EAP-${CURRENT_VERSION} at ${INSTALL_DIR}"
  if [ "${CURRENT_VERSION}" == "${VERSION}" ]
  then
    echo "You have the latest version of Phpstorm EAP already installed."
    exit
  fi
else
  echo "No PhpStorm-EAP found at ${INSTALL_DIR}"
fi

echo "Updating to Phpstorm-EAP-${VERSION}"

TMP_DIR="/tmp/PhpStorm-EAP-${VERSION}"
TMP_FILE="${TMP_DIR}/${FILE}"
EXTRACTED="${TMP_DIR}/PhpStorm-${VERSION}"

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
