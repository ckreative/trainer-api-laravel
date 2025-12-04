# Frontend Documentation

Welcome to the frontend integration documentation. This folder contains all the resources frontend developers need to integrate with the API.

## Available Guides

### [Authentication API Integration Guide](./Authentication-API-Integration-Guide.md)
Complete guide for integrating with the authentication API, including:
- Quick start and setup instructions
- Authentication flow diagrams
- Detailed endpoint documentation with examples
- Request/Response samples for all auth endpoints
- Error handling guide
- Code examples (React, Vue.js, JavaScript)
- Best practices for token management
- Troubleshooting common issues
- Testing checklist

### [Availability Schedules API Integration Guide](./Availability-Schedules-Integration-Guide.md)
Complete guide for managing user availability schedules for bookings, including:
- Quick start and setup instructions
- Detailed endpoint documentation for all CRUD operations
- Request/Response samples for schedules management
- Advanced operations (duplicate, set default)
- Error handling guide with status codes
- Code examples (React, Vue.js, TypeScript)
- Schedule validation best practices
- Timezone handling
- Testing checklist

### [Event Types API Integration Guide](./Event-Types-Integration-Guide.md)
Complete guide for managing event types (meeting types that can be booked), including:
- Quick start and setup instructions
- Detailed endpoint documentation for all 7 endpoints
- Request/Response samples for event types management
- Advanced features (multiple durations, booking limits, buffers)
- URL slug management and validation
- Advanced operations (duplicate, toggle status)
- Error handling guide with status codes
- Code examples (React, Vue.js, TypeScript with full type definitions)
- Field reference for all 25+ event type settings
- Booking limits and constraints configuration
- Testing checklist with feature-specific tests

---

## Quick Links

- **Live API Documentation**: http://localhost:8080/docs/api
- **OpenAPI Specification**: [../api/auth-api.yaml](../api/auth-api.yaml)
- **Backend Implementation**: [../IMPLEMENTATION_SUMMARY.md](../IMPLEMENTATION_SUMMARY.md)

---

## Getting Started

1. **Read the Authentication Integration Guide** to understand the authentication flow
2. **Review the OpenAPI spec** for detailed schema definitions
3. **Check the live API docs** for interactive endpoint testing
4. **Follow the code examples** in your preferred framework (React/Vue/etc.)
5. **Use the testing checklist** before deploying your integration

---

## API Base URL

- **Development**: `http://localhost:8080/api`
- **Production**: `https://api.yourapp.com/api` (update as needed)

---

## Support

For backend-related questions or issues:
- Review the backend implementation summary
- Check the API documentation
- Contact the backend development team

---

## Future API Guides

As new APIs are implemented, additional integration guides will be added to this folder:
- Bookings API Integration Guide (coming soon)
- Public Booking Pages API Integration Guide (coming soon)
- User Management API Integration Guide (coming soon)
- Notifications/Webhooks API Integration Guide (coming soon)
- etc.
