import '../css/app.scss';

/* Import Bootstrap scripts */
import 'bootstrap';
import bsCustomFileInput from 'bs-custom-file-input';
bsCustomFileInput.init();

/* Import jQuery scripts */
const $ = require('jquery');
global.$ = global.jQuery = $;

/* Import Bootstrap Tables scripts */
import 'bootstrap-table/dist/bootstrap-table.js';
import 'bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.js';
