// Team member selector component
export default function teamMemberSelector() {
    return {
        selectedMembers: [],
        maxMembers: 5,
        availableMembers: [],
        searchTerm: "",
        filteredMembers: [],

        init() {
            this.loadAvailableMembers();
        },

        async loadAvailableMembers() {
            try {
                const response = await fetch("/api/team/available-members", {
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                });
                const data = await response.json();

                if (data.success) {
                    this.availableMembers = data.data.members || [];
                    this.filterMembers();
                }
            } catch (error) {
                console.error("Error loading members:", error);
            }
        },

        filterMembers() {
            this.filteredMembers = this.availableMembers.filter((member) => {
                if (this.searchTerm) {
                    return member.name
                        .toLowerCase()
                        .includes(this.searchTerm.toLowerCase());
                }
                return true;
            });
        },

        selectMember(memberId) {
            if (this.selectedMembers.length >= this.maxMembers) {
                alert(`Maximum ${this.maxMembers} members allowed`);
                return;
            }

            if (!this.selectedMembers.includes(memberId)) {
                this.selectedMembers.push(memberId);
                this.$dispatch("member-selected", { memberId });
            }
        },

        deselectMember(memberId) {
            this.selectedMembers = this.selectedMembers.filter(
                (id) => id !== memberId,
            );
            this.$dispatch("member-deselected", { memberId });
        },

        isMemberSelected(memberId) {
            return this.selectedMembers.includes(memberId);
        },

        canSelectMore() {
            return this.selectedMembers.length < this.maxMembers;
        },

        clearSelection() {
            this.selectedMembers = [];
            this.$dispatch("selection-cleared");
        },
    };
}
