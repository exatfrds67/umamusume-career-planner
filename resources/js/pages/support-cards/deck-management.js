// Support card deck management page logic
window.pageData = window.pageData || {};

window.pageData.deckManagement = {
    selectedCards: [],
    maxCards: 6,

    init() {
        console.log("Deck management initialized");
    },

    addCard(cardId) {
        if (
            this.selectedCards.length < this.maxCards &&
            !this.selectedCards.includes(cardId)
        ) {
            this.selectedCards.push(cardId);
            this.updateDeck();
        }
    },

    removeCard(cardId) {
        this.selectedCards = this.selectedCards.filter((id) => id !== cardId);
        this.updateDeck();
    },

    updateDeck() {
        console.log("Deck updated:", this.selectedCards);
    },

    saveDeck() {
        console.log("Saving deck:", this.selectedCards);
    },
};
