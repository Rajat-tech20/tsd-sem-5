/* ==========================================================================
   AETHER AUTH - INTERACTIVE ANIMATIONS & LOGIC
   ========================================================================== */

// Initialize elements once DOM is fully loaded
document.addEventListener("DOMContentLoaded", () => {
    // Inject mobile toggle buttons dynamically for responsive layouts
    injectMobileToggles();
    
    // Stagger in the initial sign-in fields on first load
    animateFormFields('sign-in-container');
    
    // Inject SVG checkmark keyframe styles
    injectCheckmarkStyles();
});

/**
 * Programmatically injects toggle triggers for smaller/mobile screens
 * since the 3D sliding panel overlay is hidden on mobile devices.
 */
function injectMobileToggles() {
    const signInForm = document.getElementById("sign-in-form");
    const signUpForm = document.getElementById("sign-up-form");

    if (signInForm) {
        const toggleDiv = document.createElement("div");
        toggleDiv.className = "mobile-toggle-wrapper";
        toggleDiv.innerHTML = `Don't have an account? <span class="mobile-toggle-link" onclick="toggleFormState(true)">Sign Up</span>`;
        signInForm.appendChild(toggleDiv);
    }

    if (signUpForm) {
        const toggleDiv = document.createElement("div");
        toggleDiv.className = "mobile-toggle-wrapper";
        toggleDiv.innerHTML = `Already have an account? <span class="mobile-toggle-link" onclick="toggleFormState(false)">Sign In</span>`;
        signUpForm.appendChild(toggleDiv);
    }
}

/**
 * Switches the active card panel (Sign In <=> Sign Up)
 * Toggles structural CSS classes and triggers Anime.js field staggering.
 */
function toggleFormState(isSignUp) {
    const container = document.getElementById('auth-container');
    
    if (isSignUp) {
        container.classList.add('register-active');
        animateFormFields('sign-up-container');
    } else {
        container.classList.remove('register-active');
        animateFormFields('sign-in-container');
    }
}

/**
 * Creates a staggered float-in animation for form elements
 * utilizing Anime.js for smooth mathematical acceleration.
 */
function animateFormFields(containerClass) {
    const targets = document.querySelectorAll(
        `.${containerClass} .form-title, ` +
        `.${containerClass} .form-subtitle, ` +
        `.${containerClass} .social-container, ` +
        `.${containerClass} .divider-text, ` +
        `.${containerClass} .input-group, ` +
        `.${containerClass} .forgot-link, ` +
        `.${containerClass} .submit-btn, ` +
        `.${containerClass} .mobile-toggle-wrapper`
    );
    
    // Remove any running animations on these elements
    anime.remove(targets);
    
    // Animate opacity and position with a stagger delay
    anime({
        targets: targets,
        translateY: [35, 0],
        opacity: [0, 1],
        delay: anime.stagger(55, { start: 100 }),
        duration: 800,
        easing: 'cubicBezier(0.25, 1, 0.5, 1)' // Sleek easeOutQuart transition
    });
}

/**
 * Toggles the visibility of characters in password fields
 */
function togglePasswordVisibility(fieldId, button) {
    const input = document.getElementById(fieldId);
    const icon = button.querySelector("i");
    
    if (input.type === "password") {
        input.type = "text";
        icon.className = "fa-regular fa-eye-slash";
    } else {
        input.type = "password";
        icon.className = "fa-regular fa-eye";
    }

    // Minor scale pop on eye change
    anime({
        targets: icon,
        scale: [1, 1.2, 1],
        duration: 300,
        easing: 'easeInOutQuad'
    });
}

/**
 * Handles Form submissions, checks password logic, triggers card shake errors,
 * and launches Lottie loading-to-success transitions.
 */
function handleAuthSubmit(event, formType) {
    event.preventDefault();
    
    // Form verification logic
    if (formType === 'signup') {
        const password = document.getElementById("signup-password").value;
        const confirmPassword = document.getElementById("signup-confirm-password").value;
        
        if (password !== confirmPassword) {
            const containerClass = formType === 'signin' ? 'sign-in-container' : 'sign-up-container';
            triggerFormError(containerClass, document.getElementById("signup-confirm-password").parentNode);
            return;
        }
    }
    
    // If validation passes, simulate secure API submission
    triggerSuccessTransition(formType);
}

/**
 * Shakes the form container and highlights incorrect inputs using Anime.js
 */
function triggerFormError(containerClass, inputGroupToHighlight) {
    const container = document.querySelector(`.${containerClass} form`);
    
    // Highlight the input with red line if an element is passed
    if (inputGroupToHighlight) {
        const inputElement = inputGroupToHighlight.querySelector('input');
        if (inputElement) {
            inputElement.style.borderBottomColor = "#ef4444";
            
            setTimeout(() => {
                inputElement.style.borderBottomColor = "";
            }, 1500);
        }
    }
    
    // Anime.js rapid shake timeline
    anime.remove(container);
    anime({
        targets: container,
        translateX: [0, -12, 12, -10, 10, -6, 6, 0],
        duration: 500,
        easing: 'linear'
    });
}

/**
 * Injects CSS keyframe rules for the SVG checkmark animation (called once on init)
 */
function injectCheckmarkStyles() {
    const style = document.createElement('style');
    style.innerHTML = `
        @keyframes draw-circle { to { stroke-dashoffset: 0; } }
        @keyframes draw-tick { to { stroke-dashoffset: 0; } }
    `;
    document.head.appendChild(style);
}

/**
 * Renders the animated SVG checkmark into the success overlay.
 * Re-injects the SVG each time to replay the stroke-dash animation.
 */
function renderCheckmark() {
    const box = document.getElementById("lottie-box");
    box.innerHTML = `
        <svg viewBox="0 0 52 52" style="width: 100%; height: 100%;">
            <circle cx="26" cy="26" r="25" fill="none" stroke="#22c55e" stroke-width="4" stroke-dasharray="157" stroke-dashoffset="157" style="animation: draw-circle 0.6s forwards ease-in-out;"/>
            <path fill="none" stroke="#22c55e" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" d="M14 27l7.5 7.5L38 18" stroke-dasharray="48" stroke-dashoffset="48" style="animation: draw-tick 0.4s 0.6s forwards ease-in-out;"/>
        </svg>
    `;
}

/**
 * Displays the success modal and plays the SVG checkmark animation
 */
function triggerSuccessTransition(formType) {
    const overlay = document.getElementById("success-overlay");
    const title = document.getElementById("success-title");
    const desc = document.getElementById("success-desc");
    
    // Update texts depending on submission type
    if (formType === 'signin') {
        title.innerText = "Access Granted";
        desc.innerText = "Logging in to your dashboard...";
    } else {
        title.innerText = "Account Created";
        desc.innerText = "Registering user details...";
    }
    
    // Show overlay
    overlay.style.display = "flex";
    
    // Animate overlay elements entrance
    anime({
        targets: '#success-overlay',
        opacity: [0, 1],
        duration: 350,
        easing: 'easeOutQuad'
    });
    
    anime({
        targets: '.lottie-card',
        scale: [0.85, 1],
        duration: 450,
        easing: 'easeOutBack'
    });
    
    // Play SVG checkmark animation
    renderCheckmark();
    
    // Redirect/Reset simulation after animation completes
    setTimeout(() => {
        // Fade out overlay
        anime({
            targets: '#success-overlay',
            opacity: [1, 0],
            duration: 400,
            easing: 'easeInQuad',
            complete: () => {
                overlay.style.display = "none";
                // Reset form fields
                document.getElementById(`${formType === 'signin' ? 'sign-in' : 'sign-up'}-form`).reset();
                // Alert completion
                alert(`${formType === 'signin' ? 'Signed in' : 'Registered'} successfully! (Mock redirection completed)`);
            }
        });
    }, 2200);
}
