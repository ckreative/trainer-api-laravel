# Event Types API Integration Guide

## Overview

The Event Types API allows you to manage different types of meetings that can be booked on a user's calendar. This API provides comprehensive control over event scheduling, including duration options, booking limits, buffers, and availability constraints.

**Base URL:** `http://localhost:8080/api`
**Authentication:** Bearer token (JWT) - Required for all endpoints
**Content-Type:** `application/json`

## Quick Start

### Installation

```bash
npm install axios
```

### Setup

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8080/api',
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add authentication token interceptor
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('authToken');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Add error handling interceptor
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Handle unauthorized - redirect to login
      localStorage.removeItem('authToken');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

## Authentication Flow

All Event Types API endpoints require authentication:

1. User logs in via `/auth/login` endpoint
2. Server returns JWT access token
3. Store token securely (localStorage/sessionStorage)
4. Include token in `Authorization` header for all requests
5. Token format: `Bearer {token}`

## API Endpoints

### 1. Get All Event Types

**Endpoint:** `GET /event-types`
**Authentication:** Required

Get all event types for the authenticated user with optional filtering and pagination.

**Query Parameters:**
- `enabled` (boolean, optional): Filter by enabled/disabled status
- `limit` (integer, optional): Maximum number of event types to return (1-100, default: 20)
- `offset` (integer, optional): Number of event types to skip for pagination (default: 0)

**Request Example:**

```bash
curl -X GET "http://localhost:8080/api/event-types?enabled=true&limit=10" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

**Response (200 OK):**

```json
{
  "eventTypes": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "userId": "650e8400-e29b-41d4-a716-446655440000",
      "title": "30 Minute Meeting",
      "url": "30min",
      "fullUrl": "https://yourdomain.com/testtrainer/30min",
      "description": "A comprehensive discussion about your project.",
      "duration": 30,
      "enabled": true,
      "allowMultipleDurations": false,
      "multipleDurationOptions": null,
      "location": "Google Meet",
      "customLocation": null,
      "beforeEventBuffer": 5,
      "afterEventBuffer": 5,
      "minimumNotice": 120,
      "timeSlotInterval": null,
      "limitBookingFrequency": false,
      "bookingFrequencyLimit": null,
      "onlyFirstSlotPerDay": false,
      "limitTotalDuration": false,
      "totalDurationLimit": null,
      "limitUpcomingBookings": false,
      "upcomingBookingsLimit": null,
      "limitFutureBookings": false,
      "futureBookingsLimit": null,
      "availabilityScheduleId": "550e8400-e29b-41d4-a716-446655440001",
      "bookingCount": 5,
      "createdAt": "2025-01-15T10:30:00+00:00",
      "updatedAt": "2025-01-15T10:30:00+00:00"
    }
  ],
  "total": 5,
  "limit": 10,
  "offset": 0
}
```

**Error Response (401 Unauthorized):**

```json
{
  "error": "UNAUTHORIZED",
  "message": "Invalid or missing authentication token",
  "statusCode": 401,
  "timestamp": "2025-01-15T10:30:00+00:00"
}
```

---

### 2. Create Event Type

**Endpoint:** `POST /event-types`
**Authentication:** Required

Create a new event type for the authenticated user.

**Request Body (Minimal):**

```json
{
  "title": "Quick Chat",
  "url": "quick-chat",
  "duration": 15
}
```

**Request Body (Full Featured):**

```json
{
  "title": "30 Minute Consultation",
  "url": "30min",
  "description": "A comprehensive 30-minute consultation to dive deep into your project.",
  "duration": 30,
  "location": "Google Meet",
  "customLocation": null,
  "enabled": true
}
```

**Request Example:**

```bash
curl -X POST "http://localhost:8080/api/event-types" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Quick Chat",
    "url": "quick-chat",
    "duration": 15,
    "location": "Google Meet",
    "description": "A quick 15-minute video meeting."
  }'
```

**Response (201 Created):**

```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "userId": "650e8400-e29b-41d4-a716-446655440000",
  "title": "Quick Chat",
  "url": "quick-chat",
  "fullUrl": "https://yourdomain.com/testtrainer/quick-chat",
  "description": "A quick 15-minute video meeting.",
  "duration": 15,
  "enabled": true,
  "allowMultipleDurations": false,
  "multipleDurationOptions": null,
  "location": "Google Meet",
  "customLocation": null,
  "beforeEventBuffer": 0,
  "afterEventBuffer": 0,
  "minimumNotice": 120,
  "timeSlotInterval": null,
  "limitBookingFrequency": false,
  "bookingFrequencyLimit": null,
  "onlyFirstSlotPerDay": false,
  "limitTotalDuration": false,
  "totalDurationLimit": null,
  "limitUpcomingBookings": false,
  "upcomingBookingsLimit": null,
  "limitFutureBookings": false,
  "futureBookingsLimit": null,
  "availabilityScheduleId": null,
  "bookingCount": 0,
  "createdAt": "2025-01-15T10:30:00+00:00",
  "updatedAt": "2025-01-15T10:30:00+00:00"
}
```

**Error Response (400 Bad Request - Duplicate URL):**

```json
{
  "error": "DUPLICATE_URL",
  "message": "An event type with this URL already exists",
  "statusCode": 400,
  "timestamp": "2025-01-15T10:30:00+00:00"
}
```

**Error Response (422 Validation Error):**

```json
{
  "error": "VALIDATION_ERROR",
  "message": "Validation failed",
  "statusCode": 422,
  "errors": [
    {
      "field": "url",
      "message": "URL must contain only lowercase letters, numbers, and hyphens",
      "code": "INVALID_URL_FORMAT"
    }
  ],
  "timestamp": "2025-01-15T10:30:00+00:00"
}
```

---

### 3. Get Event Type by ID

**Endpoint:** `GET /event-types/{id}`
**Authentication:** Required

Get a specific event type by its ID.

**Path Parameters:**
- `id` (string, UUID): Event type ID

**Request Example:**

```bash
curl -X GET "http://localhost:8080/api/event-types/550e8400-e29b-41d4-a716-446655440000" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

**Response (200 OK):**

```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "userId": "650e8400-e29b-41d4-a716-446655440000",
  "title": "30 Minute Meeting",
  "url": "30min",
  "fullUrl": "https://yourdomain.com/testtrainer/30min",
  "description": "A comprehensive discussion about your project.",
  "duration": 30,
  "enabled": true,
  "allowMultipleDurations": true,
  "multipleDurationOptions": [15, 30, 45],
  "location": "Zoom",
  "customLocation": null,
  "beforeEventBuffer": 5,
  "afterEventBuffer": 5,
  "minimumNotice": 240,
  "timeSlotInterval": 15,
  "limitBookingFrequency": false,
  "bookingFrequencyLimit": null,
  "onlyFirstSlotPerDay": false,
  "limitTotalDuration": false,
  "totalDurationLimit": null,
  "limitUpcomingBookings": false,
  "upcomingBookingsLimit": null,
  "limitFutureBookings": true,
  "futureBookingsLimit": 60,
  "availabilityScheduleId": "550e8400-e29b-41d4-a716-446655440001",
  "bookingCount": 8,
  "createdAt": "2025-01-15T10:30:00+00:00",
  "updatedAt": "2025-01-15T15:45:00+00:00"
}
```

**Error Response (404 Not Found):**

```json
{
  "error": "NOT_FOUND",
  "message": "Event type not found",
  "statusCode": 404,
  "timestamp": "2025-01-15T10:30:00+00:00"
}
```

---

### 4. Update Event Type

**Endpoint:** `PUT /event-types/{id}`
**Authentication:** Required

Update an existing event type. All fields are optional (partial updates supported).

**Path Parameters:**
- `id` (string, UUID): Event type ID

**Request Body (Basic Update):**

```json
{
  "title": "Updated 30 Minute Meeting",
  "duration": 45
}
```

**Request Body (Advanced Update with Limits):**

```json
{
  "title": "Premium Consultation",
  "duration": 60,
  "allowMultipleDurations": true,
  "multipleDurationOptions": [30, 60, 90],
  "beforeEventBuffer": 10,
  "afterEventBuffer": 10,
  "minimumNotice": 1440,
  "limitBookingFrequency": true,
  "bookingFrequencyLimit": {
    "count": 2,
    "period": "week"
  },
  "limitUpcomingBookings": true,
  "upcomingBookingsLimit": 1,
  "limitFutureBookings": true,
  "futureBookingsLimit": 90,
  "availabilityScheduleId": "550e8400-e29b-41d4-a716-446655440001"
}
```

**Request Example:**

```bash
curl -X PUT "http://localhost:8080/api/event-types/550e8400-e29b-41d4-a716-446655440000" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Updated Meeting",
    "duration": 45,
    "beforeEventBuffer": 10
  }'
```

**Response (200 OK):**

```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "userId": "650e8400-e29b-41d4-a716-446655440000",
  "title": "Updated Meeting",
  "url": "30min",
  "fullUrl": "https://yourdomain.com/testtrainer/30min",
  "description": "A comprehensive discussion about your project.",
  "duration": 45,
  "enabled": true,
  "allowMultipleDurations": false,
  "multipleDurationOptions": null,
  "location": "Google Meet",
  "customLocation": null,
  "beforeEventBuffer": 10,
  "afterEventBuffer": 5,
  "minimumNotice": 120,
  "timeSlotInterval": null,
  "limitBookingFrequency": false,
  "bookingFrequencyLimit": null,
  "onlyFirstSlotPerDay": false,
  "limitTotalDuration": false,
  "totalDurationLimit": null,
  "limitUpcomingBookings": false,
  "upcomingBookingsLimit": null,
  "limitFutureBookings": false,
  "futureBookingsLimit": null,
  "availabilityScheduleId": "550e8400-e29b-41d4-a716-446655440001",
  "bookingCount": 8,
  "createdAt": "2025-01-15T10:30:00+00:00",
  "updatedAt": "2025-01-15T16:20:00+00:00"
}
```

---

### 5. Delete Event Type

**Endpoint:** `DELETE /event-types/{id}`
**Authentication:** Required

Delete an event type. Cannot delete event types with existing bookings.

**Path Parameters:**
- `id` (string, UUID): Event type ID

**Request Example:**

```bash
curl -X DELETE "http://localhost:8080/api/event-types/550e8400-e29b-41d4-a716-446655440000" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

**Response (200 OK):**

```json
{
  "message": "Event type deleted successfully"
}
```

**Error Response (409 Conflict):**

```json
{
  "error": "CONFLICT",
  "message": "Cannot delete event type with existing bookings",
  "statusCode": 409,
  "timestamp": "2025-01-15T10:30:00+00:00"
}
```

---

### 6. Duplicate Event Type

**Endpoint:** `POST /event-types/{id}/duplicate`
**Authentication:** Required

Create a copy of an existing event type. The duplicate will have a unique URL and start as disabled.

**Path Parameters:**
- `id` (string, UUID): Event type ID to duplicate

**Request Example:**

```bash
curl -X POST "http://localhost:8080/api/event-types/550e8400-e29b-41d4-a716-446655440000/duplicate" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

**Response (201 Created):**

```json
{
  "id": "760e8400-e29b-41d4-a716-446655440000",
  "userId": "650e8400-e29b-41d4-a716-446655440000",
  "title": "30 Minute Meeting (Copy)",
  "url": "30min-copy",
  "fullUrl": "https://yourdomain.com/testtrainer/30min-copy",
  "description": "A comprehensive discussion about your project.",
  "duration": 30,
  "enabled": false,
  "allowMultipleDurations": true,
  "multipleDurationOptions": [15, 30, 45],
  "location": "Zoom",
  "customLocation": null,
  "beforeEventBuffer": 5,
  "afterEventBuffer": 5,
  "minimumNotice": 240,
  "timeSlotInterval": 15,
  "limitBookingFrequency": false,
  "bookingFrequencyLimit": null,
  "onlyFirstSlotPerDay": false,
  "limitTotalDuration": false,
  "totalDurationLimit": null,
  "limitUpcomingBookings": false,
  "upcomingBookingsLimit": null,
  "limitFutureBookings": true,
  "futureBookingsLimit": 60,
  "availabilityScheduleId": "550e8400-e29b-41d4-a716-446655440001",
  "bookingCount": 0,
  "createdAt": "2025-01-15T16:30:00+00:00",
  "updatedAt": "2025-01-15T16:30:00+00:00"
}
```

---

### 7. Toggle Event Type Status

**Endpoint:** `PATCH /event-types/{id}/toggle`
**Authentication:** Required

Quickly enable or disable an event type.

**Path Parameters:**
- `id` (string, UUID): Event type ID

**Request Body:**

```json
{
  "enabled": true
}
```

**Request Example:**

```bash
curl -X PATCH "http://localhost:8080/api/event-types/550e8400-e29b-41d4-a716-446655440000/toggle" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{"enabled": false}'
```

**Response (200 OK):**

```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "userId": "650e8400-e29b-41d4-a716-446655440000",
  "title": "30 Minute Meeting",
  "url": "30min",
  "fullUrl": "https://yourdomain.com/testtrainer/30min",
  "description": "A comprehensive discussion about your project.",
  "duration": 30,
  "enabled": false,
  "allowMultipleDurations": true,
  "multipleDurationOptions": [15, 30, 45],
  "location": "Zoom",
  "customLocation": null,
  "beforeEventBuffer": 5,
  "afterEventBuffer": 5,
  "minimumNotice": 240,
  "timeSlotInterval": 15,
  "limitBookingFrequency": false,
  "bookingFrequencyLimit": null,
  "onlyFirstSlotPerDay": false,
  "limitTotalDuration": false,
  "totalDurationLimit": null,
  "limitUpcomingBookings": false,
  "upcomingBookingsLimit": null,
  "limitFutureBookings": true,
  "futureBookingsLimit": 60,
  "availabilityScheduleId": "550e8400-e29b-41d4-a716-446655440001",
  "bookingCount": 8,
  "createdAt": "2025-01-15T10:30:00+00:00",
  "updatedAt": "2025-01-15T16:45:00+00:00"
}
```

---

## Error Handling

### Standard Error Response Format

All error responses follow this structure:

```json
{
  "error": "ERROR_CODE",
  "message": "Human-readable error message",
  "statusCode": 400,
  "timestamp": "2025-01-15T10:30:00+00:00"
}
```

### HTTP Status Codes

| Status Code | Description |
|-------------|-------------|
| 200 | Success - Request completed successfully |
| 201 | Created - Resource created successfully |
| 400 | Bad Request - Invalid input data or duplicate URL |
| 401 | Unauthorized - Invalid or missing authentication token |
| 404 | Not Found - Resource does not exist |
| 409 | Conflict - Cannot perform action (e.g., deleting event type with bookings) |
| 422 | Validation Error - Input validation failed |
| 500 | Internal Server Error - Server-side error occurred |

### Error Codes

| Error Code | Description |
|------------|-------------|
| `UNAUTHORIZED` | Authentication token is invalid or missing |
| `NOT_FOUND` | Requested event type not found |
| `DUPLICATE_URL` | URL slug already exists for this user |
| `VALIDATION_ERROR` | Input validation failed |
| `CONFLICT` | Cannot perform action (e.g., delete event type with bookings) |
| `INTERNAL_ERROR` | An unexpected error occurred on the server |

---

## Field Reference

### Location Types

Supported location values:
- `"Google Meet"`
- `"Zoom"`
- `"Microsoft Teams"`
- `"Phone Call"`
- `"In Person"`
- `"Custom"` (requires `customLocation` field)

### Booking Frequency Limit Structure

```typescript
interface BookingFrequencyLimit {
  count: number;      // Minimum: 1
  period: 'day' | 'week' | 'month';
}
```

**Example:**
```json
{
  "count": 2,
  "period": "week"
}
```

### Total Duration Limit Structure

```typescript
interface TotalDurationLimit {
  duration: number;   // Minimum: 30 minutes
  period: 'day' | 'week' | 'month';
}
```

**Example:**
```json
{
  "duration": 240,
  "period": "day"
}
```

---

## Code Examples

### React Example

```javascript
import { useState, useEffect } from 'react';
import api from './api';

function EventTypes() {
  const [eventTypes, setEventTypes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Fetch all event types
  useEffect(() => {
    const fetchEventTypes = async () => {
      try {
        setLoading(true);
        const response = await api.get('/event-types');
        setEventTypes(response.data.eventTypes);
      } catch (err) {
        setError(err.response?.data?.message || 'Failed to fetch event types');
      } finally {
        setLoading(false);
      }
    };

    fetchEventTypes();
  }, []);

  // Create new event type
  const createEventType = async (data) => {
    try {
      const response = await api.post('/event-types', data);
      setEventTypes([...eventTypes, response.data]);
      return response.data;
    } catch (err) {
      if (err.response?.status === 400 && err.response?.data?.error === 'DUPLICATE_URL') {
        throw new Error('This URL is already in use. Please choose a different one.');
      }
      throw new Error(err.response?.data?.message || 'Failed to create event type');
    }
  };

  // Update event type
  const updateEventType = async (id, updates) => {
    try {
      const response = await api.put(`/event-types/${id}`, updates);
      setEventTypes(eventTypes.map(et => et.id === id ? response.data : et));
      return response.data;
    } catch (err) {
      throw new Error(err.response?.data?.message || 'Failed to update event type');
    }
  };

  // Delete event type
  const deleteEventType = async (id) => {
    try {
      await api.delete(`/event-types/${id}`);
      setEventTypes(eventTypes.filter(et => et.id !== id));
    } catch (err) {
      if (err.response?.status === 409) {
        throw new Error('Cannot delete event type with existing bookings');
      }
      throw new Error(err.response?.data?.message || 'Failed to delete event type');
    }
  };

  // Duplicate event type
  const duplicateEventType = async (id) => {
    try {
      const response = await api.post(`/event-types/${id}/duplicate`);
      setEventTypes([...eventTypes, response.data]);
      return response.data;
    } catch (err) {
      throw new Error(err.response?.data?.message || 'Failed to duplicate event type');
    }
  };

  // Toggle enabled status
  const toggleEventType = async (id, enabled) => {
    try {
      const response = await api.patch(`/event-types/${id}/toggle`, { enabled });
      setEventTypes(eventTypes.map(et => et.id === id ? response.data : et));
      return response.data;
    } catch (err) {
      throw new Error(err.response?.data?.message || 'Failed to toggle event type');
    }
  };

  if (loading) return <div>Loading event types...</div>;
  if (error) return <div>Error: {error}</div>;

  return (
    <div>
      <h1>Event Types</h1>
      {eventTypes.map(eventType => (
        <div key={eventType.id} className="event-type-card">
          <h3>{eventType.title}</h3>
          <p>Duration: {eventType.duration} minutes</p>
          <p>URL: {eventType.fullUrl}</p>
          <p>Status: {eventType.enabled ? 'Enabled' : 'Disabled'}</p>
          <p>Bookings: {eventType.bookingCount}</p>

          <button onClick={() => toggleEventType(eventType.id, !eventType.enabled)}>
            {eventType.enabled ? 'Disable' : 'Enable'}
          </button>
          <button onClick={() => duplicateEventType(eventType.id)}>
            Duplicate
          </button>
          <button onClick={() => deleteEventType(eventType.id)}>
            Delete
          </button>
        </div>
      ))}
    </div>
  );
}

export default EventTypes;
```

### Vue.js Example (Composition API)

```javascript
<template>
  <div>
    <h1>Event Types</h1>
    <div v-if="loading">Loading event types...</div>
    <div v-else-if="error">Error: {{ error }}</div>
    <div v-else>
      <div v-for="eventType in eventTypes" :key="eventType.id" class="event-type-card">
        <h3>{{ eventType.title }}</h3>
        <p>Duration: {{ eventType.duration }} minutes</p>
        <p>URL: {{ eventType.fullUrl }}</p>
        <p>Status: {{ eventType.enabled ? 'Enabled' : 'Disabled' }}</p>
        <p>Bookings: {{ eventType.bookingCount }}</p>

        <button @click="toggleEventType(eventType.id, !eventType.enabled)">
          {{ eventType.enabled ? 'Disable' : 'Enable' }}
        </button>
        <button @click="duplicateEventType(eventType.id)">Duplicate</button>
        <button @click="deleteEventType(eventType.id)">Delete</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from './api';

const eventTypes = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchEventTypes = async () => {
  try {
    loading.value = true;
    const response = await api.get('/event-types');
    eventTypes.value = response.data.eventTypes;
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to fetch event types';
  } finally {
    loading.value = false;
  }
};

const createEventType = async (data) => {
  try {
    const response = await api.post('/event-types', data);
    eventTypes.value.push(response.data);
    return response.data;
  } catch (err) {
    if (err.response?.status === 400 && err.response?.data?.error === 'DUPLICATE_URL') {
      throw new Error('This URL is already in use. Please choose a different one.');
    }
    throw new Error(err.response?.data?.message || 'Failed to create event type');
  }
};

const updateEventType = async (id, updates) => {
  try {
    const response = await api.put(`/event-types/${id}`, updates);
    const index = eventTypes.value.findIndex(et => et.id === id);
    if (index !== -1) {
      eventTypes.value[index] = response.data;
    }
    return response.data;
  } catch (err) {
    throw new Error(err.response?.data?.message || 'Failed to update event type');
  }
};

const deleteEventType = async (id) => {
  try {
    await api.delete(`/event-types/${id}`);
    eventTypes.value = eventTypes.value.filter(et => et.id !== id);
  } catch (err) {
    if (err.response?.status === 409) {
      throw new Error('Cannot delete event type with existing bookings');
    }
    throw new Error(err.response?.data?.message || 'Failed to delete event type');
  }
};

const duplicateEventType = async (id) => {
  try {
    const response = await api.post(`/event-types/${id}/duplicate`);
    eventTypes.value.push(response.data);
    return response.data;
  } catch (err) {
    throw new Error(err.response?.data?.message || 'Failed to duplicate event type');
  }
};

const toggleEventType = async (id, enabled) => {
  try {
    const response = await api.patch(`/event-types/${id}/toggle`, { enabled });
    const index = eventTypes.value.findIndex(et => et.id === id);
    if (index !== -1) {
      eventTypes.value[index] = response.data;
    }
    return response.data;
  } catch (err) {
    throw new Error(err.response?.data?.message || 'Failed to toggle event type');
  }
};

onMounted(() => {
  fetchEventTypes();
});
</script>
```

### TypeScript Example with Full Type Definitions

```typescript
// types.ts
interface BookingFrequencyLimit {
  count: number;
  period: 'day' | 'week' | 'month';
}

interface TotalDurationLimit {
  duration: number;
  period: 'day' | 'week' | 'month';
}

type LocationType =
  | 'Google Meet'
  | 'Zoom'
  | 'Microsoft Teams'
  | 'Phone Call'
  | 'In Person'
  | 'Custom';

interface EventType {
  id: string;
  userId: string;
  title: string;
  url: string;
  fullUrl: string;
  description: string | null;
  duration: number;
  enabled: boolean;
  allowMultipleDurations: boolean;
  multipleDurationOptions: number[] | null;
  location: LocationType | null;
  customLocation: string | null;
  beforeEventBuffer: number;
  afterEventBuffer: number;
  minimumNotice: number;
  timeSlotInterval: number | null;
  limitBookingFrequency: boolean;
  bookingFrequencyLimit: BookingFrequencyLimit | null;
  onlyFirstSlotPerDay: boolean;
  limitTotalDuration: boolean;
  totalDurationLimit: TotalDurationLimit | null;
  limitUpcomingBookings: boolean;
  upcomingBookingsLimit: number | null;
  limitFutureBookings: boolean;
  futureBookingsLimit: number | null;
  availabilityScheduleId: string | null;
  bookingCount: number;
  createdAt: string;
  updatedAt: string;
}

interface CreateEventTypeRequest {
  title: string;
  url: string;
  duration: number;
  description?: string;
  location?: LocationType;
  customLocation?: string;
  enabled?: boolean;
}

interface UpdateEventTypeRequest {
  title?: string;
  url?: string;
  duration?: number;
  description?: string;
  location?: LocationType;
  customLocation?: string;
  enabled?: boolean;
  allowMultipleDurations?: boolean;
  multipleDurationOptions?: number[];
  beforeEventBuffer?: number;
  afterEventBuffer?: number;
  minimumNotice?: number;
  timeSlotInterval?: number | null;
  limitBookingFrequency?: boolean;
  bookingFrequencyLimit?: BookingFrequencyLimit | null;
  onlyFirstSlotPerDay?: boolean;
  limitTotalDuration?: boolean;
  totalDurationLimit?: TotalDurationLimit | null;
  limitUpcomingBookings?: boolean;
  upcomingBookingsLimit?: number | null;
  limitFutureBookings?: boolean;
  futureBookingsLimit?: number | null;
  availabilityScheduleId?: string | null;
}

// api.ts
class EventTypesAPI {
  private baseURL: string;
  private token: string | null;

  constructor(baseURL: string) {
    this.baseURL = baseURL;
    this.token = localStorage.getItem('authToken');
  }

  private async request<T>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<T> {
    const url = `${this.baseURL}${endpoint}`;
    const headers = {
      'Content-Type': 'application/json',
      ...(this.token ? { Authorization: `Bearer ${this.token}` } : {}),
      ...options.headers,
    };

    const response = await fetch(url, {
      ...options,
      headers,
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Request failed');
    }

    return response.json();
  }

  async getEventTypes(params?: {
    enabled?: boolean;
    limit?: number;
    offset?: number;
  }): Promise<{
    eventTypes: EventType[];
    total: number;
    limit: number;
    offset: number;
  }> {
    const queryParams = new URLSearchParams();
    if (params?.enabled !== undefined) {
      queryParams.append('enabled', String(params.enabled));
    }
    if (params?.limit) {
      queryParams.append('limit', String(params.limit));
    }
    if (params?.offset) {
      queryParams.append('offset', String(params.offset));
    }

    const query = queryParams.toString();
    return this.request(`/event-types${query ? `?${query}` : ''}`);
  }

  async createEventType(data: CreateEventTypeRequest): Promise<EventType> {
    return this.request('/event-types', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async getEventTypeById(id: string): Promise<EventType> {
    return this.request(`/event-types/${id}`);
  }

  async updateEventType(
    id: string,
    updates: UpdateEventTypeRequest
  ): Promise<EventType> {
    return this.request(`/event-types/${id}`, {
      method: 'PUT',
      body: JSON.stringify(updates),
    });
  }

  async deleteEventType(id: string): Promise<{ message: string }> {
    return this.request(`/event-types/${id}`, {
      method: 'DELETE',
    });
  }

  async duplicateEventType(id: string): Promise<EventType> {
    return this.request(`/event-types/${id}/duplicate`, {
      method: 'POST',
    });
  }

  async toggleEventType(id: string, enabled: boolean): Promise<EventType> {
    return this.request(`/event-types/${id}/toggle`, {
      method: 'PATCH',
      body: JSON.stringify({ enabled }),
    });
  }
}

// Usage
const api = new EventTypesAPI('http://localhost:8080/api');

// Fetch event types
const { eventTypes } = await api.getEventTypes({ enabled: true });

// Create event type
const newEventType = await api.createEventType({
  title: 'Quick Chat',
  url: 'quick-chat',
  duration: 15,
  location: 'Google Meet',
});

// Update with advanced features
const updated = await api.updateEventType(newEventType.id, {
  allowMultipleDurations: true,
  multipleDurationOptions: [15, 30, 45],
  limitBookingFrequency: true,
  bookingFrequencyLimit: {
    count: 2,
    period: 'week',
  },
});

// Toggle status
await api.toggleEventType(newEventType.id, false);

// Duplicate
const duplicate = await api.duplicateEventType(newEventType.id);

// Delete
await api.deleteEventType(newEventType.id);
```

---

## Best Practices

### 1. URL Slug Generation

Create SEO-friendly and user-friendly URL slugs:

```javascript
const generateUrlSlug = (title) => {
  return title
    .toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
    .replace(/\s+/g, '-')          // Replace spaces with hyphens
    .replace(/-+/g, '-')           // Replace multiple hyphens with single
    .trim();
};

// Example
const slug = generateUrlSlug('30 Minute Meeting'); // "30-minute-meeting"
```

### 2. Duration Validation

Validate duration before sending to API:

```javascript
const validateDuration = (duration) => {
  if (duration < 5) {
    throw new Error('Duration must be at least 5 minutes');
  }
  if (duration > 480) {
    throw new Error('Duration cannot exceed 8 hours (480 minutes)');
  }
  return true;
};
```

### 3. Multiple Duration Options

Ensure duration options are valid and sorted:

```javascript
const validateMultipleDurations = (options) => {
  // Check all options are valid
  options.forEach(duration => {
    if (duration < 5 || duration > 480) {
      throw new Error('All duration options must be between 5 and 480 minutes');
    }
  });

  // Sort ascending
  return options.sort((a, b) => a - b);
};

// Example
const options = validateMultipleDurations([45, 15, 30]); // [15, 30, 45]
```

### 4. Booking Limit Configuration

Helper to create booking limits:

```javascript
const createBookingFrequencyLimit = (count, period) => {
  if (count < 1) {
    throw new Error('Count must be at least 1');
  }
  if (!['day', 'week', 'month'].includes(period)) {
    throw new Error('Period must be day, week, or month');
  }
  return { count, period };
};

// Example
const limit = createBookingFrequencyLimit(2, 'week');
// { count: 2, period: 'week' }
```

### 5. Error Handling

Handle all error scenarios:

```javascript
const handleEventTypeError = (error) => {
  const statusCode = error.response?.status;
  const errorData = error.response?.data;

  switch (statusCode) {
    case 400:
      if (errorData?.error === 'DUPLICATE_URL') {
        return 'This URL is already in use. Please choose a different one.';
      }
      return 'Invalid input data';
    case 401:
      return 'Please log in to continue';
    case 404:
      return 'Event type not found';
    case 409:
      return 'Cannot delete event type with existing bookings';
    case 422:
      return errorData?.message || 'Validation failed';
    default:
      return 'An unexpected error occurred';
  }
};
```

### 6. Full URL Display

Generate shareable booking URLs:

```javascript
const getBookingUrl = (eventType, baseUrl = 'https://yourdomain.com') => {
  return `${baseUrl}/${eventType.user.username}/${eventType.url}`;
};
```

---

## Testing Checklist

### Endpoint Testing

- [ ] Get all event types - success case
- [ ] Get all event types - with enabled filter
- [ ] Get all event types - with pagination
- [ ] Create event type - success case (minimal)
- [ ] Create event type - success case (full featured)
- [ ] Create event type - duplicate URL error
- [ ] Create event type - validation errors
- [ ] Get event type by ID - success case
- [ ] Get event type by ID - not found
- [ ] Update event type - basic fields
- [ ] Update event type - advanced features (limits, buffers)
- [ ] Update event type - duplicate URL error
- [ ] Update event type - partial update
- [ ] Delete event type - success case
- [ ] Delete event type - conflict (has bookings)
- [ ] Duplicate event type - success case
- [ ] Duplicate event type - URL uniqueness
- [ ] Toggle event type - enable
- [ ] Toggle event type - disable

### Feature Testing

- [ ] Multiple duration options work correctly
- [ ] Booking frequency limits are enforced
- [ ] Total duration limits are enforced
- [ ] Upcoming bookings limits are enforced
- [ ] Future bookings limits are enforced
- [ ] Buffers are applied correctly
- [ ] Minimum notice is enforced
- [ ] Time slot intervals work correctly
- [ ] Availability schedule integration works

### Error Scenarios

- [ ] Unauthorized access (missing token)
- [ ] Unauthorized access (invalid token)
- [ ] Invalid URL format (uppercase, spaces, special chars)
- [ ] Duplicate URL for same user
- [ ] Invalid duration (< 5 or > 480)
- [ ] Invalid location type
- [ ] Missing custom location when type is Custom
- [ ] Invalid booking limit configurations
- [ ] Non-existent availability schedule ID

### Integration Verification

- [ ] Token refresh on expiration
- [ ] Proper error handling for all status codes
- [ ] Loading states during API calls
- [ ] Optimistic UI updates
- [ ] Rollback on error
- [ ] URL slug auto-generation
- [ ] Duplicate URL handling

---

## Additional Resources

- **Duration Guidelines:** 5-480 minutes (8 hours max)
- **URL Slug Format:** Lowercase letters, numbers, hyphens only
- **Location Types:** [IANA Time Zone Database](https://www.iana.org/time-zones) for timezone-aware scheduling
- **HTTP Status Codes:** [MDN Web Docs](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status)
- **Laravel Sanctum:** [Official Documentation](https://laravel.com/docs/sanctum)

---

**Generated:** 2025-11-11
**API Version:** 1.0.0
**Last Updated:** 2025-11-11
