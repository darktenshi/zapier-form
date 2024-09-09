### Project Report: Zillow Data Integration via RapidAPI with Manual Override

#### Objective:
The goal is to integrate **Zillow** data using the **Zillow56 API** via **RapidAPI** to retrieve property details, specifically square footage, based on the user's input. The form will offer both an automatic data retrieval process and a manual entry option for square footage if the user chooses to override the API data.

### 1. **Overview of the API Integration**:
We will use the **Zillow56 API** to retrieve real-time square footage data when users input their property address. If the API successfully finds matching data, the form will automatically populate the address and square footage fields. If no data is found, or the user prefers to enter their own square footage, they can manually override the API results.

### 2. **Process Flow**:

#### **1. Address Input with Autocomplete**:
- **Step 1**: When the user begins typing their address, the system will query the Zillow56 API and provide autocomplete suggestions based on the entered address.
- **Step 2**: The user selects the correct address from the suggestions, which will auto-fill the form’s address fields (e.g., street, city, ZIP code).

#### **2. Fetching and Displaying Square Footage**:
- **Step 3**: After the user selects an address, the API will attempt to retrieve the square footage and display it in the form. The retrieved square footage will be **non-editable** to ensure data integrity.
  
#### **3. Manual Square Footage Entry**:
- **Step 4**: If the user disagrees with the API-provided square footage or if no data is found, they can click a button that activates a **manual square footage input field**. Once activated, the user can enter their own square footage value, overriding the API-provided data.
  
#### **4. Data Validation and Form Submission**:
- **Step 5**: Before submission, the form will ensure only one square footage value (API-provided or manually entered) is submitted. If both fields are filled, the system will prioritize the **manual entry**.

### 3. **Technical Implementation**:

#### **1. API Key Setup**:
- **API Authentication**: Retrieve the Zillow56 API key from your RapidAPI account and store it in the plugin’s admin settings.
- **Admin Configuration**: Provide an option in the WordPress admin panel to enable or disable the Zillow API and store the API key securely.

**Admin Panel Example**:
```php
add_settings_field(
    'zillow_api_key',
    'Zillow API Key',
    'render_zillow_api_key_field',
    'zapier_form_settings',
    'zapier_form_settings_section'
);

function render_zillow_api_key_field() {
    $zillow_api_key = get_option('zillow_api_key', '');
    echo '<input type="text" name="zillow_api_key" value="' . esc_attr($zillow_api_key) . '" />';
}
```

#### **2. Address Input and Autocomplete**:
- **Autocomplete**: Implement a RESTful API call to Zillow56 when the user starts entering their address. Display matching address suggestions in a dropdown.
  
**Example Autocomplete Request**:
```javascript
const options = {
    method: 'GET',
    headers: {
        'X-RapidAPI-Key': '<your-api-key>',
        'X-RapidAPI-Host': 'zillow56.p.rapidapi.com'
    }
};

fetch('https://zillow56.p.rapidapi.com/search?address=123+Main+St&citystatezip=Denver,CO,80202', options)
    .then(response => response.json())
    .then(data => console.log(data))
    .catch(err => console.error(err));
```

#### **3. Square Footage Display and Non-Editable Field**:
- Once the user selects an address, the API will attempt to retrieve and auto-fill the square footage. This field will be displayed but **non-editable** to prevent users from altering API-provided data.
  
#### **4. Manual Entry Option**:
- If the API doesn’t find any square footage data or the user prefers to enter it themselves, they can click a button that unlocks a **manual input field** for square footage.
  
**Example of Manual Entry Button**:
```javascript
document.getElementById('manualEntryBtn').addEventListener('click', function() {
    document.getElementById('manualSquareFootageField').disabled = false; // Enable manual input
});
```

#### **5. Validation and Submission**:
- The form will ensure only one of the two fields (API square footage or manual entry) is sent on form submission. If both are filled, prioritize the **manual entry**.

**Submission Handling**:
```php
if (!empty($_POST['manual_square_footage'])) {
    $square_footage = sanitize_text_field($_POST['manual_square_footage']);
} else {
    $square_footage = sanitize_text_field($_POST['api_square_footage']);
}
```

### 4. **User Experience Considerations**:
- **Clear Instructions**: Provide the user with clear instructions on how the system retrieves square footage and how they can manually override it if necessary.
- **Non-Editable API Data**: This ensures that users do not alter data automatically provided by the API unless they explicitly opt to enter it manually.
- **Fallback for Missing Data**: If the API fails to find any data, the system will gracefully allow manual entry without interrupting the user’s workflow.

### 5. **Security and Error Handling**:
- **API Key Protection**: Store the API key securely in the WordPress database, ensuring it isn’t exposed in the front-end code.
- **Error Handling**: In the event of API errors (e.g., rate limit exceeded or no data found), fallback logic will ensure users can manually enter their information.
- **Validation**: Ensure the form validates the square footage field and prevents submission of both fields simultaneously.

### 6. **Testing and Performance**:
- **Test Autocomplete Functionality**: Verify that the address autocomplete and API data retrieval work seamlessly for various properties in the Denver metro area.
- **Test Manual Override**: Ensure users can manually enter square footage and that the form correctly submits either API-provided or manual data, but not both.
- **Error Scenarios**: Simulate API errors (e.g., incorrect API key, no data found) to ensure the user experience is smooth and fallback mechanisms work.

### Conclusion:
This setup will create a flexible yet robust form workflow where users can benefit from Zillow’s real-time data but still have the option to enter their own square footage if necessary. The system will ensure clean, validated data is submitted to either **MaidCentral** or **Zapier**, with only one square footage value being stored (either API-provided or manually entered).

Would you like to move forward with this approach, or is there any part you’d like to modify or explore further?