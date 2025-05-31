document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('#form-register-resend-confirmation'),
    formMsg = document.querySelector('#form-register-resend-confirmation-msg');

  if (!form) {
    return;
  }

  form.addEventListener('click', function (e) {
    e.preventDefault();
    const formData = new FormData(form);
    const url = form.getAttribute('action');
    const method = form.getAttribute('method');

    fetch(url, {
      method: method,
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    })
      .then((response) => response.json())
      .then((data) => {
        formMsg.textContent = data.message;
        formMsg.className = 'text-center mt-2';
        if (data.status === 'success') {
          formMsg.classList.add('alert', 'alert-success');
        } else if (data.status === 'info') {
          formMsg.classList.add('alert', 'alert-info');
        } else {
          formMsg.classList.add('alert', 'alert-danger');
        }
      })
      .catch(() => {
        formMsg.textContent = "Erreur lors de la tentative d'envoi.";
        formMsg.className = 'alert alert-danger';
      });
  });
});
