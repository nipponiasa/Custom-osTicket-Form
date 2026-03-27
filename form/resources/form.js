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

    // Populate agent selects from USERS data (available only in agent role).
    if (typeof USERS !== 'undefined') {
        const agentOrgSelect  = document.getElementById('agent_organization');
        const agentUserSelect = document.getElementById('agent_user');

        if (agentOrgSelect && agentUserSelect) {
            // Build unique organizations list from USERS.
            const orgs = new Map();
            USERS.forEach(function (user) {
                if (user.org_id && !orgs.has(user.org_id)) {
                    orgs.set(user.org_id, user.org_name);
                }
            });

            // Populate agent_organization with placeholder + unique orgs.
            const orgPlaceholder = document.createElement('option');
            orgPlaceholder.value = '';
            orgPlaceholder.textContent = agentOrgSelect.dataset.placeholder;
            agentOrgSelect.appendChild(orgPlaceholder);

            orgs.forEach(function (orgName, orgId) {
                const option = document.createElement('option');
                option.value = orgId;
                option.textContent = orgName;
                agentOrgSelect.appendChild(option);
            });

            // Populate agent_user with placeholder + all users (initially all hidden).
            const userPlaceholder = document.createElement('option');
            userPlaceholder.value = '';
            userPlaceholder.textContent = agentUserSelect.dataset.placeholder;
            agentUserSelect.appendChild(userPlaceholder);

            USERS.forEach(function (user) {
                const option = document.createElement('option');
                option.value = user.email;
                option.textContent = user.name;
                option.dataset.email   = user.email;
                option.dataset.name    = user.name;
                option.dataset.company = user.org_name ?? '';
                if (user.org_id) {
                    option.dataset.orgId = user.org_id;
                }
                option.classList.add('d-none');   // hidden until an org is selected
                agentUserSelect.appendChild(option);
            });

            // Fill user-fields inputs when an agent_user is selected.
            agentUserSelect.addEventListener('change', function () {
                const selected = this.options[this.selectedIndex];
                document.getElementById('email').value        = selected.dataset.email   ?? '';
                document.getElementById('name').value         = selected.dataset.name    ?? '';
                document.getElementById('organization').value = selected.dataset.company ?? '';
            });

            // Filter agent_user options when agent_organization changes.
            agentOrgSelect.addEventListener('change', function () {
                const selectedOrgId = this.value;

                agentUserSelect.querySelectorAll('option[data-org-id]').forEach(function (option) {
                    const matches = selectedOrgId && option.dataset.orgId === selectedOrgId;
                    option.classList.toggle('d-none', !matches);
                });

                // Reset user select to placeholder.
                agentUserSelect.value = '';
            });
        }
    }
});

