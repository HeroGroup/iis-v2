const App = require('node-windows').Service

const svc = new App({
  name:'IIS-Listener',
  description: 'Intelligent Irrigation System Listener on MQTT',
  script: 'C:\\inetpub\\wwwroot\\iis-v2-web\\Listener\\app.js',
});

svc.on('install',function() {
  svc.start();
});

svc.install();
