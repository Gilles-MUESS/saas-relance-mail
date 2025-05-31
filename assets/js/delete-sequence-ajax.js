import { Modal } from 'bootstrap';

document.addEventListener('DOMContentLoaded', function () {
  const deleteBtns = document.querySelectorAll('.delete-sequence-btn'),
    deleteForm = document.querySelector('#deleteSequenceForm');

  if (!deleteBtns || !deleteForm) return;

  // Ajuster l'url d'action du formulaire de suppression
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.delete-sequence-btn');
    if (!btn) return;
    e.preventDefault();

    const sequenceId = btn.getAttribute('data-sequence-id');
    const newAction = deleteForm
      .getAttribute('action')
      .replace('sequenceId', sequenceId);
    deleteForm.setAttribute('action', newAction);
  });

  // Réinitialiser l'url d'action du formulaire de suppression si la modale se ferme
  document
    .getElementById('deleteSequenceModal')
    .addEventListener('hidden.bs.modal', function () {
      deleteForm.setAttribute('action', originalAction);
    });

  // Appel ajax pour supprimer la séquence
  deleteForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const sequenceId = deleteForm.getAttribute('action').match(/(\d+)$/)[0];
    const modal = document.getElementById('deleteSequenceModal');

    fetch(deleteForm.getAttribute('action'), {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': deleteForm.querySelector('input[name="_token"]').value,
      },
      body: JSON.stringify({ sequenceId: sequenceId }),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then((data) => {
        // Gérer la réponse de succès
        if (data.success) {
          const sequenceRow = document.querySelector(
            `tr[data-sequence-id="${sequenceId}"]`
          );

          if (sequenceRow) {
            sequenceRow.remove();
          }

          // Fermer la modale
          if (modal) {
            const bsModal = Modal.getInstance(modal);
            if (bsModal) {
              bsModal.hide();
            }
          }
        } else {
          //display an error message in the modal
          const errorMessage = document.querySelector(
            '#deleteSequenceModal .modal-body'
          );
          const errorEl = document.createElement('div');
          errorEl.className = 'alert alert-danger';
          errorEl.textContent =
            data.message || "La séquence n'a pu être supprimée.";
          errorMessage.appendChild(errorEl);

          if (modal) {
            setTimeout(() => {
              const bsModal = Modal.getInstance(modal);
              if (bsModal) {
                bsModal.hide();
              }
            }, 4000);
          }
        }
      })
      .catch((error) => {
        console.error('Error:', error);
      });
  });
});
