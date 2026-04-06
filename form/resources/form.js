// Define a custom element <required-field> that can be used to indicate required fields in forms.
customElements.define('required-field', class extends HTMLElement {
    connectedCallback() {
        this.innerHTML = '<span class="text-danger" aria-hidden="true"> *</span>';
    }
});

// Helper function Q
window.Q = (selector) => {
    if ( selector.startsWith('#') ) {  
          let element = document.querySelector(selector);   
          if (!element) {return null} 
          element.set ??= function(content){element.textContent=content;return element};
          element.show ??= function(condition=true){if (condition) {element.classList.remove('d-none')} else {element.classList.add('d-none')} return element};
          element.on ??= function(event,callback){element.addEventListener(event,callback);return element};
          return element;
    } else {
          if (selector.startsWith('~')) {selector=`[data-variable=${selector.substring(1)}]`}
          let elements = [...document.querySelectorAll(selector)];
          elements.set ??= function(content){elements.forEach(el=>el.textContent=content)};
          elements.show ??= function(condition=true){elements.forEach(el=>{
              if (condition) {el.classList.remove('d-none')} else {el.classList.add('d-none')}
          })};
          elements.on ??= function(event,action, options={}){
                  document.addEventListener(event,(e)=>{const t=e.target.closest(selector);if(t){action.call(t,e)}}, options);
          }
          return elements;
    }
};







document.addEventListener('DOMContentLoaded', function () {


    //# Adjust navigation links

    // Στο Navigation bar, τροποποιούμε το "Open a Ticket" link και προσθέτουμε ένα νέο για "Open a Ticket - Agent"
    // O λόγος που γίνονται με αυτόν τον τρόπο είναι γιατι πρέπει να εφαρμόζεται και στα ισπανικά, καθώς και σε custom classes που έχουν προτεθεί.
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

    // Όλα τα υπόλοιπα links προς open.php — μόνο στη root σελίδα
    const path = window.location.pathname;
    const isRoot = path === '/' || /\/(index\.php)?$/.test(path);
    if (isRoot) {
        Q('a[href$="open.php"]').forEach(function (link) {
            link.href = '/new.php';
        });
    }



    //# VIN search — fetch ERP data and populate model/color/order_no fields.
    Q('#vin')?.on('input', function () {
        this.classList.remove('is-valid', 'is-invalid');
    });

    // Prevent Enter from submitting the form unless the submit button is focused.
    Q('form')?.on('keydown', function (e) {
        if (e.key !== 'Enter' || document.activeElement?.type === 'submit') return;
        e.preventDefault();
        // Trigger VIN search if Enter is pressed while focused on the VIN field.
        if (document.activeElement?.id === 'vin') Q('#btn-vin-search').click();
    });

    Q('#btn-vin-search')?.on('click', function () {
        const vin = Q('#vin').value.trim();

        Q('#error-message').show(false);

        const role     = document.querySelector('input[name="role"]')?.value ?? 'client';
        const endpoint = '/form/vin_lookup.php' + (role === 'agent' ? '?role=agent' : '');

        Q('#btn-vin-search').disabled = true;

        fetch(endpoint, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ vin }),
        })
            .then(async (res) => {
                const text = await res.text();
                let data;
                try { data = JSON.parse(text); } catch { throw new Error('Invalid server response: ' + text.slice(0, 200)); }
                return { ok: res.ok, data };
            })
            .then(({ ok, data }) => {
                if (!ok) {
                    Q('#error-message').set(data.error ?? 'Unknown error').show();
                    Q('#vin').classList.remove('is-valid');
                    Q('#vin').classList.add('is-invalid');
                    return;
                }
                Q('#model').value    = data.model    ?? '';
                Q('#color').value    = data.color    ?? '';
                Q('#order_no').value = data.order_no ?? '';
                Q('#vin').classList.remove('is-invalid');
                Q('#vin').classList.add('is-valid');
            })
            .catch(function (err) {
                Q('#error-message').set(err.message).show();
                Q('#vin').classList.remove('is-valid');
                Q('#vin').classList.add('is-invalid');
            })
            .finally(function () {
                Q('#btn-vin-search').disabled = false;
            });
    });




    //# Populate agent selects from USERS data (available only in agent role).
    if (window.USERS && Q('#agent_organization') && Q('#agent_user')) {

        // Build unique organizations list from USERS.
        const orgs = new Map();
        const USERS = window.USERS;   // For easier reference in this block.
        USERS.forEach(user => {
            if (user.org_id && !orgs.has(user.org_id)) orgs.set(user.org_id, user.org_name);
        });

        // Populate agent_organization with placeholder + unique orgs.
        const orgPlaceholder = document.createElement('option');
        orgPlaceholder.value = '';
        orgPlaceholder.textContent = Q('#agent_organization').dataset.placeholder;
        Q('#agent_organization').appendChild(orgPlaceholder);

        orgs.forEach((orgName, orgId) => {
            const option = document.createElement('option');
            option.value = orgId;
            option.textContent = orgName;
            Q('#agent_organization').appendChild(option);
        });

        // Populate agent_user with placeholder + all users (initially all hidden).
        const userPlaceholder = document.createElement('option');
        userPlaceholder.value = '';
        userPlaceholder.textContent = Q('#agent_user').dataset.placeholder;
        Q('#agent_user').appendChild(userPlaceholder);

        USERS.forEach(user => {
            const option = document.createElement('option');
            option.value = user.email;
            option.textContent = user.name;
            option.dataset.email   = user.email;
            option.dataset.name    = user.name;
            option.dataset.company = user.org_name ?? '';
            if (user.org_id) option.dataset.orgId = user.org_id;
            option.classList.add('d-none');   // hidden until an org is selected
            Q('#agent_user').appendChild(option);
        });

        // Fill user-fields inputs when an agent_user is selected.
        Q('#agent_user').on('change', function () {
            const selected = this.options[this.selectedIndex];
            Q('#email').value        = selected.dataset.email   ?? '';
            Q('#name').value         = selected.dataset.name    ?? '';
            Q('#organization').value = selected.dataset.company ?? '';
        });

        // Filter agent_user options when agent_organization changes.
        Q('#agent_organization').on('change', function () {
            Q('#agent_user').querySelectorAll('option[data-org-id]').forEach(option => {
                option.classList.toggle('d-none', !this.value || option.dataset.orgId !== this.value);
            });
            Q('#agent_user').value = '';   // Reset user select to placeholder.
        });
    }




});

