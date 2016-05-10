all:

install:
	mkdir -p $(DESTDIR)/etc/slack-icinga2
	mkdir -p $(DESTDIR)/usr/share/slack-icinga2/commands
	mkdir -p $(DESTDIR)/var/log/slack-icinga2
	install -m 644 etc/config.json $(DESTDIR)/etc/slack-icinga2
	install -m 644 src/*.php $(DESTDIR)/usr/share/slack-icinga2
	install -m 644 src/commands/*.php $(DESTDIR)/usr/share/slack-icinga2/commands
