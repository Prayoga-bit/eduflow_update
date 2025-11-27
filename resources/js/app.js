import './bootstrap';
import '../css/app.css';
import Alpine from 'alpinejs';

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Close flash messages
window.closeFlash = function(flashId) {
    const flash = document.getElementById(flashId);
    if (flash) {
        flash.style.display = 'none';
    }
};

// Initialize any tooltips, modals, etc.
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any JavaScript plugins here
    // Example: Initialize tooltips
    // const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    // tooltipTriggerList.map(function (tooltipTriggerEl) {
    //     return new bootstrap.Tooltip(tooltipTriggerEl);
    // });
    
    // Initialize modals if they exist
    const noteModal = document.getElementById('createNoteModal');
    if (noteModal) {
        const myModal = new bootstrap.Modal(noteModal);
    }
    
    console.log('App initialized');
});