#!/bin/bash

echo -n "0000:00:1a.0" | tee /sys/bus/pci/drivers/ehci-pci/unbind
echo -n "0000:00:1d.0" | tee /sys/bus/pci/drivers/ehci-pci/unbind
echo -n "0000:00:14.0" | tee /sys/bus/pci/drivers/xhci_hcd/unbind
echo -n "0000:00:1a.0" | tee /sys/bus/pci/drivers/ehci-pci/bind
echo -n "0000:00:1d.0" | tee /sys/bus/pci/drivers/ehci-pci/bind
echo -n "0000:00:14.0" | tee /sys/bus/pci/drivers/xhci_hcd/bind
