import '../styles/new-sequence.scss';
import 'select2/dist/js/select2.min.js';

(function ($) {
  $(document).ready(function () {
    $('.select2-enable').select2();
  });

  $('#recipient-form').on('submit', function (event) {
    event.preventDefault();

    var $form = $(this);
    $.ajax({
      url: $form.attr('action'),
      method: 'POST',
      data: $form.serialize(),
      success: function (response) {
        if (response.success) {
          // Ajoute la nouvelle option à select2
          var newOption = new Option(response.text, response.id, true, true);
          $('#sequence_recipient').append(newOption).trigger('change');
          // Ferme la modale
          $('#addSequenceAddRecipient').modal('hide');
          // Reset le formulaire si besoin
          $form[0].reset();
        } else {
          // Affiche les erreurs
          alert(response.errors.join('\n'));
        }
      },
      error: function (xhr) {
        // Gestion des erreurs serveur
        alert("Erreur lors de l'ajout du destinataire.");
      },
    });
  });

  $('#label-form').on('submit', function (event) {
    event.preventDefault();

    var $form = $(this);
    $.ajax({
      url: $form.attr('action'),
      method: 'POST',
      data: $form.serialize(),
      success: function (response) {
        if (response.success) {
          // Ajoute la nouvelle option au select
          var $select = $('#sequence_label');
          // Si c'est un <select> classique :
          var newOption = new Option(response.text, response.id, true, true);
          $select.append(newOption).trigger('change');
          // Ferme la modale
          $('#addSequenceAddLabel').modal('hide');
          // Reset le formulaire si besoin
          $form[0].reset();
        } else {
          // Affiche les erreurs
          alert(response.errors.join('\n'));
        }
      },
    });
  });

  document.addEventListener('DOMContentLoaded', function () {
    let messagesList = document.getElementById('row messages-list');
    let addButton = document.getElementById('add-message');
    let index = messagesList.querySelectorAll('.message-item').length;

    addButton.addEventListener('click', function (e) {
      e.preventDefault();
      let prototype = messagesList.getAttribute('data-prototype');
      let newForm = prototype.replace(/__name__/g, index);

      let newDiv = document.createElement('div');
      newDiv.classList.add('message-item');
      newDiv.innerHTML = newForm;

      // Bouton suppression
      let removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'btn btn-danger btn-sm mb-3';
      removeBtn.innerText = 'Supprimer ce message';
      removeBtn.onclick = function () {
        newDiv.remove();
      };
      newDiv.appendChild(removeBtn);

      messagesList.appendChild(newDiv);
      index++;
    });
  });
})(jQuery);
