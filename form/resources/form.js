customElements.define('required-field', class extends HTMLElement {
    connectedCallback() {
        this.innerHTML = '<span class="text-danger" aria-hidden="true"> *</span>';
    }
});

// Στο Navigation bar, τροποποιούμε το "Open a Ticket" link και προσθέτουμε ένα νέο για "Open a Ticket - Agent"
// O λόγος που γίνονται με αυτόν τον τρόπο είναι γιατι πρέπει να εφαρμόζεται και στα ισπανικά, καθώς και σε custom classes που έχουν προτεθεί.
function adjustNavigation() {
    ['nav', 'nav-mobile'].forEach(function (navId) {        // Για κάθε navbar (desktop και mobile)
        const nav = document.getElementById(navId);
        if (!nav) return;

        const openLink = nav.querySelector('a[href="/open.php"]');     
        if (openLink) {
            openLink.href = '/new.php';     // Τροποποιούμε το υπάρχον link για τους πελάτες

            const originalLi = openLink.closest('li');
            if (originalLi) {
                const agentLi = originalLi.cloneNode(true);     // Duplicate το υπάρχον li με το link
                const agentLink = agentLi.querySelector('a');
                agentLink.textContent += ' - Agent';
                agentLink.href = '/new.php?role=agent';
                originalLi.insertAdjacentElement('afterend', agentLi);
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    adjustNavigation();

    // Όλα τα υπόλοιπα links προς open.php — μόνο στη root σελίδα
    const path = window.location.pathname;
    const isRoot = path === '/' || /\/(index\.php)?$/.test(path);
    if (isRoot) {
        document.querySelectorAll('a[href$="open.php"]').forEach(function (link) {
            link.href = '/new.php';
        });
    }
});

