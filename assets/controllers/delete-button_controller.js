import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  connect() {
    /**
     * @type {HTMLButtonElement}
     */
    const button = this.element;

    const confirmationMessage =
      button.dataset.confirmation || "Confirmez-vous la suppression ?";

    button.addEventListener("click", (e) => {
      const deleteConfirmed = confirm(confirmationMessage);

      if (!deleteConfirmed) {
        e.preventDefault();
      }
    });
  }
}
