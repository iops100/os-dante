{% if helpers.exists('OPNsense.dante.sockdglobal.global.enabled') and OPNsense.dante.sockdglobal.global.enabled|default("0") == "1" %}
dante_var_script="/usr/local/opnsense/scripts/OPNsense/Dante/setup.sh"
dante_config="/usr/local/etc/Dante/sockd.conf"
sockd_enable="YES"
{% else %}
sockd_enable="NO"
{% endif %}
