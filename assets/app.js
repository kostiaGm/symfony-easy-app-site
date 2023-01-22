/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
//import './styles/app.css';

import './styles/global.scss'

const $ = require('jquery');
window.$ = window.jQuery = global.$ = global.jQuery = $;

// start the Stimulus application
import './bootstrap';

import * as bootstrap from 'bootstrap';
import {includes} from "core-js/internals/array-includes";
window.bootstrap = bootstrap;

import moment from 'moment';
window.moment = moment;
const ko = require("knockout/build/output/knockout-latest");
window.ko = ko;
const daterangepicker = require('knockout-daterangepicker/dist/daterangepicker.js');
window.daterangepicker = daterangepicker;

$(document).ready(function() {
    $('#page_filter_createdAt').daterangepicker({
        forceUpdate: true,

        callback: function(startDate, endDate, period){
            var title = startDate.format(conf.datetime_format) + ' – ' + endDate.format(conf.datetime_format);
            $(this).val(title)
        }
    });

    $('#page_filter_updatedAt').daterangepicker({
        forceUpdate: true,
        callback: function(startDate, endDate, period){
            var title = startDate.format(conf.datetime_format) + ' – ' + endDate.format(conf.datetime_format);
            $(this).val(title)
        }
    });

});


