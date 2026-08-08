document.addEventListener('DOMContentLoaded', function() {

  // Toast notification system
  window.showToast = function(message, type) {
    type = type || 'info';
    var container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    var toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(function() {
      if (toast.parentNode) toast.parentNode.removeChild(toast);
    }, 4000);
  };

  // Form validation
  window.validateForm = function(formId) {
    var form = document.getElementById(formId);
    if (!form) return true;
    var valid = true;
    var inputs = form.querySelectorAll('[required]');
    inputs.forEach(function(input) {
      if (!input.value.trim()) {
        valid = false;
        input.style.borderColor = '#ef4444';
        input.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.1)';
        var errorEl = input.parentElement.querySelector('.field-error');
        if (errorEl) errorEl.classList.add('visible');
      } else {
        input.style.borderColor = '';
        input.style.boxShadow = '';
        var errorEl = input.parentElement.querySelector('.field-error');
        if (errorEl) errorEl.classList.remove('visible');
      }
    });
    return valid;
  };

  // Password match validation
  window.validatePasswordMatch = function(passwordId, confirmId) {
    var password = document.getElementById(passwordId);
    var confirm = document.getElementById(confirmId);
    if (!password || !confirm) return true;
    if (password.value !== confirm.value) {
      confirm.style.borderColor = '#ef4444';
      confirm.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.1)';
      var errorEl = confirm.parentElement.querySelector('.field-error');
      if (errorEl) {
        errorEl.textContent = 'Les mots de passe ne correspondent pas';
        errorEl.classList.add('visible');
      }
      return false;
    }
    return true;
  };

  // Password strength indicator
  window.initPasswordStrength = function(inputId, barId) {
    var input = document.getElementById(inputId);
    var bar = document.getElementById(barId);
    if (!input || !bar) return;
    input.addEventListener('input', function() {
      var val = input.value;
      var strength = 0;
      if (val.length >= 6) strength += 25;
      if (val.length >= 10) strength += 15;
      if (/[A-Z]/.test(val)) strength += 20;
      if (/[0-9]/.test(val)) strength += 20;
      if (/[^A-Za-z0-9]/.test(val)) strength += 20;
      var pct = Math.min(strength, 100);
      bar.style.width = pct + '%';
      if (pct < 40) bar.style.background = '#ef4444';
      else if (pct < 70) bar.style.background = '#f59e0b';
      else bar.style.background = '#10b981';
    });
  };

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      var target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // Initialize password strength on change password page
  initPasswordStrength('newpassword', 'pwd-strength-bar');

  // Auto-dismiss alert messages after 5 seconds
  var alerts = document.querySelectorAll('.errorWrap, .succWrap');
  alerts.forEach(function(alert) {
    setTimeout(function() {
      alert.style.opacity = '0';
      alert.style.transform = 'translateX(20px)';
      alert.style.transition = 'all 0.3s ease';
      setTimeout(function() {
        if (alert.parentNode) alert.parentNode.removeChild(alert);
      }, 300);
    }, 5000);
  });

  // Add fade-in class to cards for animation
  var cards = document.querySelectorAll('.card');
  cards.forEach(function(card, index) {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'opacity 0.5s ease ' + (index * 0.1) + 's, transform 0.5s ease ' + (index * 0.1) + 's';
    setTimeout(function() {
      card.style.opacity = '1';
      card.style.transform = 'translateY(0)';
    }, 100);
  });

});