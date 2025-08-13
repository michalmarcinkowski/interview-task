## Invoice Structure:

The invoice should contain the following fields:
* **Invoice ID**: Auto-generated during creation.
* **Invoice Status**: Possible states include `draft,` `sending,` and `sent-to-client`.
* **Customer Name** 
* **Customer Email** 
* **Invoice Product Lines**, each with:
  * **Product Name**
  * **Quantity**: Integer, must be positive. 
  * **Unit Price**: Integer, must be positive.
  * **Total Unit Price**: Calculated as Quantity x Unit Price. 
* **Total Price**: Sum of all Total Unit Prices.

## Required Endpoints:

1. **View Invoice**: Retrieve invoice data in the format above.
2. **Create Invoice**: Initialize a new invoice.
3. **Send Invoice**: Handle the sending of an invoice.

## Functional Requirements:

### Invoice Criteria:

* An invoice can only be created in `draft` status. 
* An invoice can be created with empty product lines. 
* An invoice can only be sent if it is in `draft` status. 
* An invoice can only be marked as `sent-to-client` if its current status is `sending`. 
* To be sent, an invoice must contain product lines with both quantity and unit price as positive integers greater than **zero**.

### Invoice Sending Workflow:

* **Send an email notification** to the customer using the `NotificationFacade`. 
  * The email's subject and message may be hardcoded or customized as needed. 
  * Change the **Invoice Status** to `sending` after sending the notification.

### Delivery:

* Upon successful delivery by the Dummy notification provider:
  * The **Notification Module** triggers a `ResourceDeliveredEvent` via webhook.
  * The **Invoice Module** listens for and captures this event.
  * The **Invoice Status** is updated from `sending` to `sent-to-client`.
  * **Note**: This transition requires that the invoice is currently in the `sending` status.

## Technical Architecture:

### High-Level Architecture

The codebase follows **Domain-Driven Design (DDD)** principles with a **modular, layered architecture**:

```
┌─────────────────────────────────────────────────────────────────┐
│                        Presentation Layer                       │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐ │
│  │   Controllers   │  │     Routes      │  │   Middleware    │ │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                                │
┌─────────────────────────────────────────────────────────────────┐
│                      Application Layer                          │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐ │
│  │    Services     │  │    Commands     │  │    Facades      │ │
│  │    Handlers     │  │    Factories    │  │   Interfaces    │ │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                                │
┌─────────────────────────────────────────────────────────────────┐
│                        Domain Layer                             │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐ │
│  │     Models      │  │   ValueObjects  │  │   Exceptions    │ │
│  │     Enums       │  │   Repositories  │  │   Interfaces    │ │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                                │
┌─────────────────────────────────────────────────────────────────┐
│                    Infrastructure Layer                         │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐ │
│  │   Persistence   │  │    Services     │  │   Providers     │ │
│  │   Eloquent      │  │    Drivers      │  │   Event Bus     │ │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

### Module Structure

The system is organized into **feature modules** with clear boundaries:

#### **Invoices Module** (`src/Modules/Invoices/`)
```
Invoices/
├── Application/         # Use cases, services, commands
├── Domain/              # Business logic, models, rules
├── Infrastructure/      # Persistence, external services
└── Presentation/        # HTTP controllers, routes, DTOs
```

#### **Notifications Module** (`src/Modules/Notifications/`)
```
Notifications/
├── Api/                 # Shared contracts, events, DTOs
├── Application/         # Facades, services
├── Infrastructure/      # Drivers, webhook simulation
└── Presentation/        # Controllers, routes
```

### Design Patterns & Principles

#### **Clean Architecture**
- **Dependency Inversion**: High-level modules don't depend on low-level modules
- **Interface Segregation**: Clients depend only on interfaces they use
- **Single Responsibility**: Each class has one reason to change

#### **Domain-Driven Design**
- **Aggregate Roots**: `Invoice` manages its own state and business rules
- **Value Objects**: `Email`, `Quantity`, `UnitPrice` encapsulate business concepts

#### **Event-Driven Architecture**
- **Event Dispatcher**: Laravel's event system for module communication
- **Event Listeners**: `InvoiceDeliveredListener` handles cross-module events
- **Loose Coupling**: Modules communicate via events, not direct dependencies

### Complete Invoice Delivery Workflow

The system implements a complete event-driven delivery workflow that simulates real-world notification delivery:

```
1. Create Invoice → Status: DRAFT
2. Send Invoice → Status: DRAFT → SENDING
3. DummyDriver "sends" notification → Returns success
4. WebhookSimulator calls webhook → /notification/hook/delivered/{invoiceId}
5. Webhook triggers → ResourceDeliveredEvent dispatched
6. InvoiceDeliveredListener handles event → Status: SENDING → SENT_TO_CLIENT
```

### Key Components

#### **Notification Module**
- **`NotificationFacade`**: Orchestrates notification sending and webhook simulation
- **`DummyDriver`**: Simulates external notification service (SendGrid, Mailgun, etc.)
- **`WebhookSimulator`**: Simulates external service calling our webhook endpoint
- **`WebhookSimulatorInterface`**: Contract for webhook simulation (Application Layer)
- **`WebhookSimulator`**: HTTP client implementation (Infrastructure Layer)

#### **Invoice Module**
- **`SendInvoiceHandler`**: Manages invoice sending workflow
- **`InvoiceDeliveredListener`**: Listens for delivery confirmation events
- **`Invoice` Domain Model**: Enforces business rules and status transitions

#### **Event System**
- **`ResourceDeliveredEvent`**: Dispatched when webhook confirms delivery
- **Event Listener**: Automatically updates invoice status upon delivery confirmation

## Technical Requirements:

* **Preferred Approach**: Domain-Driven Design (DDD) is preferred for this project. If you have experience with DDD, please feel free to apply this methodology. However, if you have a different, comparable project or task that showcases your skills, you may submit that instead of creating this task.
* **Alternative Submission**: If you have a different, comparable project or task that showcases your skills, you may submit that instead of creating this task.
* **Unit Tests**: Core invoice logic should be unit tested. Testing the returned values from endpoints is not required.
* **Documentation**: Candidates are encouraged to document their decisions and reasoning in comments or a README file, explaining why specific implementations or structures were chosen.

## Setup Instructions:

* Start the project by running `./start.sh`.
* To access the container environment, use: `docker compose exec app bash`.

## Testing the Complete Workflow

To test the complete invoice delivery workflow:

1. **Create an invoice** via POST `/api/invoices`
2. **Check initial status** via GET `/api/invoices/{id}` → Status: "draft"
3. **Send the invoice** via POST `/api/invoices/{id}/send`
4. **Status change** to "sending"
5. **WebhookSimulator automatically calls** the delivery webhook
6. **Check final status** via GET `/api/invoices/{id}` → Status: "sent-to-client"

The system automatically simulates the complete delivery flow, making it easy to test and verify the entire workflow without external dependencies.
