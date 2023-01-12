/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
//import './styles/app.css';

import './styles/global.scss'

// start the Stimulus application
import './bootstrap';

import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import { createPopper } from '@popperjs/core';
const popcorn = document.querySelector('[data-bs-toggle="popover"]');
const tooltip = document.querySelector('[data-bs-toggle="popover"]');


createPopper(popcorn, tooltip, {
    placement: 'top',
});



const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))

