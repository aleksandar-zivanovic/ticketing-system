<?php

/**
 * Renders a partial input field using the input.php template.
 * This function sanitizes the input parameters to prevent XSS attacks.
 * 
 * @param string|null $label: The text label for the input field.
 * @param string $name: The name attribute for the input field. It should match the corresponding session variable if using $_SESSION for persistent input.
 * @param string $type: The type of the input field (e.g., text, password).
 * @param string|null $placeholder: The placeholder text for the input field.
 * @param string|int|null $value Value of a `value` atribute.
 * @param string|null $atributes HTML atributte for adding to input (e.g. pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" required).
 * 
 * @note: Ensure that the name attribute of the input matches the session variable name 
 * if you intend to use $_SESSION to pre-fill the input with previously submitted data.
 */
function renderingInputField(
    ?string $label,
    string $name,
    string $type,
    ?string $placeholder,
    string|int|null $value = null,
    ?string $atributes = null
): void {
    $label       = $label ? htmlspecialchars($label) : null;
    $name        = htmlspecialchars($name);
    $type        = htmlspecialchars($type);
    $placeholder = $placeholder ? htmlspecialchars($placeholder) : null;
    $value       = $value ? htmlspecialchars($value) : null;
    $atributes   = $atributes ? htmlspecialchars($atributes) : null;

    if ($type == 'hidden' && empty($value)) {
        throw new InvalidArgumentException("If type='hidden', value must be string or integer.");
    }

    if ($type == 'hidden' && !empty($placeholder)) {
        throw new InvalidArgumentException("placeholder should not exists.");
    }

    require ROOT . 'views' . DS . 'partials' . DS . '_input.php';
}

/** 
 * Renders a checkbox field with an optional label and link description.
 * This function sanitizes the checkbox parameters to prevent XSS attacks.
 * 
 * @param string $name The name attribute for the checkbox field.
 * @param ?string $id The id attribute for the checkbox field. If null, the id will default to the value of $name.
 * @param string $agreeText The text for the checkbox label.
 * @param ?string $agreeUrl The URL of the link in the label description. (optional)
 * @param ?string $agreeUrlDescription The text for the link in the label description. (optional)
 *
 * Example usage:
 * renderingCheckboxField('terms', null, 'I agree to the <strong>Terms and Conditions</strong>', 'https://example.com/terms', 'Terms and Conditions');
 */
function renderingCheckboxField(
    string $name,
    string $agreeText,
    ?string $id = null,
    ?string $agreeUrl = null,
    ?string $agreeUrlDescription = null
): void {
    $name                 = htmlspecialchars($name);
    $id                   = !$id ? $name : htmlspecialchars($id);
    $agreeText            = htmlspecialchars($agreeText);
    $agreeUrl             = $agreeUrl ? htmlspecialchars($agreeUrl) : null;
    $agreeUrlDescription  = $agreeUrlDescription ? htmlspecialchars($agreeUrlDescription) : null;

    require ROOT . 'views' . DS . 'partials' . DS . '_input_checkbox.php';
}

/**
 * Renders a partial text area field using the textArea.php template.
 * This function sanitizes the input parameters to prevent XSS attacks.
 * 
 * @param string|null $label: The text label for the input field.
 * @param string $name: The name attribute for the input field. It should match the corresponding session variable if using $_SESSION for persistent input.
 * 
 * @note: Ensure that the name attribute of the input matches the session variable name 
 * if you intend to use $_SESSION to pre-fill the input with previously submitted data.
 */
function renderingTextArea(?string $label, string $name): void
{
    $label = $label ? htmlspecialchars($label) : null;
    $name  = htmlspecialchars($name);

    require ROOT . 'views' . DS . 'partials' . DS . '_textArea.php';
}

/**
 * Renders a partial text area field using the textArea.php template.
 * This function sanitizes the input parameters to prevent XSS attacks.
 * 
 * @param string|null $label: The text label for the input field.
 * @param string $name: The name attribute for the input field. It should match the corresponding session variable if using $_SESSION for persistent input.
 * 
 * @note: Ensure that the name attribute of the input matches the session variable name 
 * if you intend to use $_SESSION to pre-fill the input with previously submitted data.
 */
function renderingSelectOption(?string $label, string $name, array $data): void
{
    $label = $label ? htmlspecialchars($label) : null;
    $name  = htmlspecialchars($name);

    require ROOT . 'views' . DS . 'partials' . DS . '_select_option.php';
}

/** 
 * Renders a button element.
 * 
 * @param string $name The name attribute for the button and element id value. If type is "submit", it will be used as the name attribute of the <input> element.
 * @param string $value The text displayed on the button. If type is "submit", it will be used as the value of value attribute of the <input> element.
 * @param string $textColor Tailwind CSS class for text color (e.g., "text-white").
 * @param string $bgColor Tailwind CSS class for background color (e.g., "bg-blue-600").
 * @param string $hoverBgColor Tailwind CSS class for hover background color (e.g., "hover:bg-blue-700").
 * @param string $otherClasses Additional Tailwind CSS classes for custom styling (optional).
 * @param string $otherAttributes Additional HTML attributes for the button (optional).
 * @param string $icon Material Design Icon class (e.g., "mdi mdi-ticket") to display on the button (optional).
 * @param string $link The URL to navigate to when the button is clicked (required if type is "link").
 * @param string $type The type of button: "submit", "button", "reset", or "link". Default is "submit".
 * @throws InvalidArgumentException If the provided type is not one of the allowed values or if link is missing for type "link".
 */
function renderingButton(
    string $name,
    string $value,
    string $textColor = "text-white",
    string $bgColor = "bg-blue-600",
    string $hoverBgColor = "hover:bg-blue-700",
    string $otherClasses = "",
    string $otherAttributes = "",
    string $icon = "",
    string $link = "",
    string $type = "submit"
): void {
    if (!in_array($type, ["submit", "button", "reset", "link"])) {
        throw new InvalidArgumentException("Invalid button type: $type");
    }

    if ($type === "link" && empty($link)) {
        throw new InvalidArgumentException("Link type button requires a valid link.");
    }

    if ($type !== "link" && !empty($link)) {
        throw new InvalidArgumentException("Only link type button can have a link.");
    }

    // Load the appropriate partial based on the button type
    require ROOT . 'views' . DS . 'partials' . DS . ($type === "submit" ? '_input_submit.php' : '_button.php');
}

/**
 * Renders a single dashboard card in the admin panel.
 *   
 * @param string $icon. Icon code for Materila Design Icons. 
 * 
 * @param string $label      Name of the ticket category shown on the card (e.g. "Solved").
 * @param int|string $count  Value shown on the card.
 * @param string $iconColor  Tailwind CSS class for icon color (e.g. "text-blue-500").
 * @param string $icon       Material Design Icon class (e.g. "mdi-ticket").
 */

function renderDashboardCard(
    string $label,
    int|string $count,
    string $iconColor,
    string $icon
) {
    include ROOT . 'views' . DS . 'partials' . DS . '_dashboard_card_widget.php';
}

function renderDashboardCardsRow(
    string $label,
    int $total,
    int $processing,
    int $solved,
    ?int $waiting = null
): void {
    include ROOT . 'views' . DS . 'partials' . DS . '_dashboard_cards.php';
}

/**
 * Renders chart.
 * 
 * @param string $title Chart name.
 * @param string $type Chart type (e.g. "line", "bar", etc.).
 * @param array $data Array of data prepared for rendering the chart.
 *   Structure:
 *   - 'labels': array of strings — labels for the X-axis (e.g. months).
 *   - 'datasets': array of arrays — each dataset includes:
 *       - 'label': string — name of the dataset (e.g. ticket status).
 *       - 'data': array of integers — integer values matching the labels.
 *
 * Example:
 * [
 *   'labels' => ['Jan', 'Feb', 'Mar', ..., 'Dec'],
 *   'datasets' => [
 *     [
 *       'label' => 'Open',
 *       'data'  => [12, 7, 3, ...]
 *     ],
 *     // More datasets...
 *   ]
 * ]
 * 
 * @return void
 */
function renderChart(string $title, string $type, array $data): void
{
    $chartId = 'chart_' . uniqid();
    include ROOT . 'views' . DS . 'partials' . DS . '_dashboard_chart.php';
}

/** 
 * Renders a legend for a table.
 * 
 * @param string $greenTitle Title for the green legend item.
 * @param string $blueTitle Title for the blue legend item.
 * @param string $whiteTitle Title for the white legend item.
 * @return void
 */
function renderTableLegend(string $greenTitle, string $blueTitle, string $whiteTitle): void
{
    include ROOT . 'views' . DS . 'partials' . DS . '_table_legend.php';
}
