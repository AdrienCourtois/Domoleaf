# Get master's version in control file
VERSION_MASTER := $(shell grep "Version: " glmaster/DEBIAN/control | sed 's/ //g' | cut -d : -f 2)

# Get slave version in control file
VERSION_SLAVE := $(shell grep "Version: " glslave/DEBIAN/control | sed 's/ //g' | cut -d : -f 2)

# Get arch
ARCH := $(shell uname -m)

# Master's package name
MASTER_NAME = glmaster_$(VERSION_MASTER)_all.deb

ifeq ($(ARCH),x86_64)
ARCH_NAME := amd64
ARM_ARCH_NAME := armhf
else
ifeq ($(findstring armv5,$(ARCH)),armv5)
ARCH_NAME := armel
else
ifeq ($(findstring arm,$(ARCH)),arm)
ARCH_NAME := armhf
else
@echo -e " [ \033[31mko\033[0m ] Architecture not supported"
exit 1
endif
endif
endif

# Slave package name, different by arch
SLAVE_NAME        = glslave_$(VERSION_SLAVE)_$(ARCH_NAME).deb
SLAVE_NAME_NATIVE = glslave_$(VERSION_SLAVE)_$(ARM_ARCH_NAME).deb

# Main rule to call compilation rules and generate deb packages
# If X64 compile for X64 and ARMHF
ifeq ($(ARCH),x86_64)
all: compile packages compile-native packages-native
else # Else, compile for native arch
all: compile-native packages-native
endif

# ARMHF compilation rules
compile:
	@echo "[ \033[33m..\033[0m ] Compiling for $(ARM_ARCH_NAME)..."
	@export CC=arm-linux-gnueabihf-gcc # Tel to GCC to compile for ARMHF
	@./check_compiler
	cd eibd && ./build.sh
	@cp eibd/eibdbuild/build/libeibclient.a  /usr/lib
	@cp eibd/eibdbuild/build/libeibclient.la /usr/lib
	@cp eibd/eibdbuild/build/libpthsem.a     /usr/lib
	@cp eibd/eibdbuild/build/libpthsem.la    /usr/lib
	@cp eibd/eibdbuild/build/pthsem.pc       /usr/lib
	@make -C monitor_knx
	@make -C monitor_enocean
	@echo "[ \033[32mok\033[0m ] Done compiling for ARM"

# Native compilation rules
compile-native:
	@echo "[ \033[33m..\033[0m ] Compiling for native architecture $(ARCH_NAME)..."
	@export CC=gcc
	@./check_compiler
	@cd eibd && ./build.sh
	@cp eibd/eibdbuild/build/libeibclient.a  /usr/lib
	@cp eibd/eibdbuild/build/libeibclient.la /usr/lib
	@cp eibd/eibdbuild/build/libpthsem.a     /usr/lib
	@cp eibd/eibdbuild/build/libpthsem.la    /usr/lib
	@cp eibd/eibdbuild/build/pthsem.pc       /usr/lib
	@make -C monitor_knx
	@make -C monitor_enocean
	@echo "[ \033[32mok\033[0m ] Done compiling for native architecture"

# Rule to generate debian packages for native arch
packages-native:
	@cp monitor_knx/monitor_knx              glslave/usr/bin
	@cp monitor_enocean/monitor_enocean      glslave/usr/bin
	@cp monitor_enocean/monitor_enocean.cfg  glslave/etc/greenleaf
	@cp eibd/eibdbuild/build/eibd            glslave/usr/bin
	@cp eibd/eibdbuild/build/groupread       glslave/usr/bin
	@cp eibd/eibdbuild/build/groupwrite      glslave/usr/bin
	@cp eibd/eibdbuild/build/groupswrite     glslave/usr/bin
	@cp eibd/eibdbuild/build/vbusmonitor1    glslave/usr/bin
	@cp eibd/eibdbuild/build/libeibclient.a  glslave/usr/lib
	@cp eibd/eibdbuild/build/libeibclient.la glslave/usr/lib
	@cp eibd/eibdbuild/build/libpthsem.a     glslave/usr/lib
	@cp eibd/eibdbuild/build/libpthsem.la    glslave/usr/lib
	@cp eibd/eibdbuild/build/pthsem.pc       glslave/usr/lib
	@rm -rf glmaster/etc/greenleaf/www
	@mkdir -p glmaster/etc/greenleaf
	@sed -i "s/define('VERSION', '0.0.0');/define('VERSION', '$(VERSION_MASTER)');/g" www/config.php
	@cp -r www glmaster/etc/greenleaf/
	@echo $(VERSION_MASTER) > glmaster/etc/greenleaf/.glmaster.version
	@sed -i "s/define('VERSION', '$(VERSION_MASTER)');/define('VERSION', '0.0.0');/g" www/config.php
	@dpkg-deb --build glmaster > /dev/null
	@echo $(VERSION_SLAVE) > glslave/etc/greenleaf/.glslave.version
	@sed -i 's/Architecture: all/Architecture: $(ARCH_NAME)/g' glslave/DEBIAN/control # Modify arch
	@dpkg-deb --build glslave > /dev/null
	@sed -i 's/Architecture: $(ARCH_NAME)/Architecture: all/g' glslave/DEBIAN/control # Reset arch
	@mv glmaster.deb         $(MASTER_NAME)
	@mv glslave.deb          $(SLAVE_NAME)

# Rule to generate debian packages for ARMHF
packages:
	@cp monitor_knx/monitor_knx              glslave/usr/bin
	@cp monitor_enocean/monitor_enocean      glslave/usr/bin
	@cp monitor_enocean/monitor_enocean.cfg  glslave/etc/greenleaf
	@cp eibd/eibdbuild/build/eibd            glslave/usr/bin
	@cp eibd/eibdbuild/build/groupread       glslave/usr/bin
	@cp eibd/eibdbuild/build/groupwrite      glslave/usr/bin
	@cp eibd/eibdbuild/build/groupswrite     glslave/usr/bin
	@cp eibd/eibdbuild/build/vbusmonitor1    glslave/usr/bin
	@cp eibd/eibdbuild/build/libeibclient.a  glslave/usr/lib
	@cp eibd/eibdbuild/build/libeibclient.la glslave/usr/lib
	@cp eibd/eibdbuild/build/libpthsem.a     glslave/usr/lib
	@cp eibd/eibdbuild/build/libpthsem.la    glslave/usr/lib
	@cp eibd/eibdbuild/build/pthsem.pc       glslave/usr/lib
	@rm -rf glmaster/etc/greenleaf/www
	@mkdir -p glmaster/etc/greenleaf
	@sed -i "s/define('VERSION', '0.0.0');/define('VERSION', '$(VERSION_MASTER)');/g" www/config.php
	@cp -r www glmaster/etc/greenleaf/
	@echo $(VERSION_MASTER) > glmaster/etc/greenleaf/.glmaster.version
	@sed -i "s/define('VERSION', '$(VERSION_MASTER)');/define('VERSION', '0.0.0');/g" www/config.php
	@dpkg-deb --build glmaster > /dev/null
	@echo $(VERSION_SLAVE) > glslave/etc/greenleaf/.glslave.version
	@sed -i 's/Architecture: all/Architecture: $(ARM_ARCH_NAME)/g' glslave/DEBIAN/control # Modify arch
	@dpkg-deb --build glslave > /dev/null
	@sed -i 's/Architecture: $(ARM_ARCH_NAME)/Architecture: all/g' glslave/DEBIAN/control # Reset arch
	@mv glmaster.deb         $(MASTER_NAME)
	@mv glslave.deb          $(SLAVE_NAME_NATIVE)

# Clean rules
clean:
	@make -C monitor_knx clean
	@make -C monitor_enocean clean
	@rm -f $(MASTER_NAME)
	@rm -f $(SLAVE_NAME)
	@rm -f override
	@rm -f Packages

.PHONY: all packages install clean
