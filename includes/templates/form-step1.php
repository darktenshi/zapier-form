<form id="zapier-form-step1" class="zapier-form">
    <?php wp_nonce_field('zapier_form_nonce', 'zapier_form_nonce'); ?>
    <div class="form-grid">
        <div class="form-field">
            <input type="text" id="FirstName" name="FirstName" required placeholder=" ">
            <label for="FirstName">First Name</label>
        </div>
        <div class="form-field">
            <input type="text" id="LastName" name="LastName" required placeholder=" ">
            <label for="LastName">Last Name</label>
        </div>
        <div class="form-field">
            <input type="email" id="Email" name="Email" required placeholder=" ">
            <label for="Email">Email</label>
        </div>
        <div class="form-field">
            <input type="tel" id="Phone" name="Phone" required placeholder=" ">
            <label for="Phone">Phone</label>
        </div>
        <div class="form-field">
            <input type="text" id="Zip" name="Zip" required placeholder=" ">
            <label for="Zip">Zip Code</label>
        </div>
    </div>
    <div class="form-submit">
        <button type="submit" class="zapier-form-button">
            Next Step
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</form>
