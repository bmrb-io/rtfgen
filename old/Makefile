# Makefile for rtfgen

include $(BMRBMAKEHEADER)

#
# uncomment (and edit) these if your $BMRBMAKEHEADER does not
# define them (or you don't have $BMRBMAKEHEADER)
#
#PERL_PATH=\/usr\/bin\/perl
#BMRBBASE=/bmrb
#BMRBBASE_SED=\/bmrb
#GLOBALCLASSES=$(BMRBBASE)/javaclasses
#GLOBALCLASSES_SED=$(BMRBBASE_SED)\/javaclasses
#DOCUMENTROOT=$(BMRBBASE)/htdocs
#DOCUMENTROOT_SED=$(BMRBBASE_SED)\/htdocs
#CGIBINDIR=$(DOCUMENTROOT)/cgi-bin
#
# path to NMR-STAR files
#
NMRSTARDIR=$(DOCUMENTROOT_SED)\/data_library\/files
#
# CLASSPATH
#
JAVACLASSDIR=.:$(GLOBALCLASSES_SED)
#
# install dirs
#
INSTCLASSES=$(GLOBALCLASSES)/EDU/bmrb/rtfgen
INSTHTML=$(DOCUMENTROOT)/rtfgen
#
# dirs
#
LOGDIR=$(BMRBADMIN_SED)\/logs
TMPDIR=\/tmp

all: rtfgen cgi

rtfgen: 
	javac EDU/bmrb/rtfgen/*.java

cgi: rtfgen.pl
	cat rtfgen.pl | $(SED) 's/PERL_PATH/$(PERL_PATH)/g' | \
	$(SED) 's/JAVAPATH_REPLACEME/$(JAVA_PATH)/g' | \
	$(SED) 's/JAVA_CLASS_FILES/$(JAVACLASSDIR)/g' | \
	$(SED) 's/DATA_LIBRARY_FILES/$(NMRSTARDIR)/g' | \
	$(SED) 's/LOGDIR_REPLACEME/$(LOGDIR)/g' | \
	$(SED) 's/TMPDIR_REPLACEME/$(TMPDIR)/g' > rtfgen.cgi
	chmod a+x rtfgen.cgi

oldcgi: rtfgen-web.pl
	cat rtfgen-web.pl | $(SED) 's/PERL_PATH/$(PERL_PATH)/g' | \
	$(SED) 's/DATA_LIBRARY_FILES/$(NMRSTARDIR)/g' | \
	$(SED) 's/JAVA_CLASS_FILES/$(JAVACLASSDIR)/g' | \
	cat - > rtfgen_web.cgi
	chmod a+x rtfgen_web.cgi

install: install-rtfgen install-cgi install-html

install-rtfgen: EDU/bmrb/rtfgen/rtfgen.class
	- mkdir -p $(INSTCLASSES)
	cp EDU/bmrb/rtfgen/*.class $(INSTCLASSES)

install-cgi: cgi
	cp rtfgen.cgi $(CGIBINDIR)

install-oldcgi: cgi
	cp rtfgen_web.cgi $(CGIBINDIR)

install-html: index.html
	- mkdir -p $(INSTHTML)
	cp *.html *.gif $(INSTHTML)

clean:
	- rm EDU/bmrb/rtfgen/*.class *\~ *.cgi

# eof Makefile
