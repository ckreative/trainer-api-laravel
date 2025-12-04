# Availability Schedules API Integration Guide

## Overview

The Availability Schedules API allows you to manage user availability schedules for bookings. This API enables creating, updating, deleting, and duplicating weekly availability schedules with customizable time slots for each day.

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

All Availability Schedules API endpoints require authentication:

1. User logs in via `/auth/login` endpoint
2. Server returns JWT access token
3. Store token securely (localStorage/sessionStorage)
4. Include token in `Authorization` header for all requests
5. Token format: `Bearer {token}`

## API Endpoints

### 1. Get All Availability Schedules

**Endpoint:** `GET /availability-schedules`
**Authentication:** Required

Get all availability schedules for the authenticated user with optional filtering and pagination.

**Query Parameters:**
- `isDefault` (boolean, optional): Filter by default schedule
- `limit` (integer, optional): Maximum number of schedules to return (1-100, default: 20)
- `offset` (integer, optional): Number of schedules to skip for pagination (default: 0)

**Request Example:**

```bash
curl -X GET "http://localhost:8080/api/availability-schedules?limit=10&offset=0" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

**Response (200 OK):**

```json
{
  "schedules": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "userId": "650e8400-e29b-41d4-a716-446655440000",
      "name": "Working Hours",
      "isDefault": true,
      "timezone": "America/New_York",
      "schedule": [
        {
          "day": "Monday",
          "enabled": true,
          "slots": [
            {
              "start": "09:00",
              "end": "12:00"
            },
            {
              "start": "13:00",
              "end": "17:00"
            }
          ]
        },
        {
          "day": "Tuesday",
          "enabled": true,
          "slots": [
            {
              "start": "09:00",
              "end": "17:00"
            }
          ]
        },
        {
          "day": "Wednesday",
          "enabled": false,
          "slots": []
        },
        {
          "day": "Thursday",
          "enabled": true,
          "slots": [
            {
              "start": "09:00",
              "end": "17:00"
            }
          ]
        },
        {
          "day": "Friday",
          "enabled": true,
          "slots": [
            {
              "start": "09:00",
              "end": "15:00"
            }
          ]
        },
        {
          "day": "Saturday",
          "enabled": false,
          "slots": []
        },
        {
          "day": "Sunday",
          "enabled": false,
          "slots": []
        }
      ],
      "eventTypeCount": 3,
      "createdAt": "2025-01-15T10:30:00+00:00",
      "updatedAt": "2025-01-15T10:30:00+00:00"
    }
  ],
  "total": 3,
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

### 2. Create Availability Schedule

**Endpoint:** `POST /availability-schedules`
**Authentication:** Required

Create a new availability schedule for the authenticated user.

**Request Body:**

```json
{
  "name": "Working Hours",
  "isDefault": false,
  "timezone": "America/New_York",
  "schedule": [
    {
      "day": "Sunday",
      "enabled": false,
      "slots": []
    },
    {
      "day": "Monday",
      "enabled": true,
      "slots": [
        {
          "start": "09:00",
          "end": "12:00"
        },
        {
          "start": "13:00",
          "end": "17:00"
        }
      ]
    },
    {
      "day": "Tuesday",
      "enabled": true,
      "slots": [
        {
          "start": "09:00",
          "end": "17:00"
        }
      ]
    },
    {
      "day": "Wednesday",
      "enabled": true,
      "slots": [
        {
          "start": "09:00",
          "end": "17:00"
        }
      ]
    },
    {
      "day": "Thursday",
      "enabled": true,
      "slots": [
        {
          "start": "09:00",
          "end": "17:00"
        }
      ]
    },
    {
      "day": "Friday",
      "enabled": true,
      "slots": [
        {
          "start": "09:00",
          "end": "15:00"
        }
      ]
    },
    {
      "day": "Saturday",
      "enabled": false,
      "slots": []
    }
  ]
}
```

**Request Example:**

```bash
curl -X POST "http://localhost:8080/api/availability-schedules" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Working Hours",
    "isDefault": false,
    "timezone": "America/New_York",
    "schedule": [...]
  }'
```

**Response (201 Created):**

```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "userId": "650e8400-e29b-41d4-a716-446655440000",
  "name": "Working Hours",
  "isDefault": false,
  "timezone": "America/New_York",
  "schedule": [...],
  "eventTypeCount": 0,
  "createdAt": "2025-01-15T10:30:00+00:00",
  "updatedAt": "2025-01-15T10:30:00+00:00"
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
      "field": "schedule[1].slots[0].start",
      "message": "Start time must be before end time",
      "code": "INVALID_TIME_RANGE"
    }
  ],
  "timestamp": "2025-01-15T10:30:00+00:00"
}
```

---

### 3. Get Availability Schedule by ID

**Endpoint:** `GET /availability-schedules/{id}`
**Authentication:** Required

Get a specific availability schedule by its ID.

**Path Parameters:**
- `id` (string, UUID): Availability schedule ID

**Request Example:**

```bash
curl -X GET "http://localhost:8080/api/availability-schedules/550e8400-e29b-41d4-a716-446655440000" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

**Response (200 OK):**

```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "userId": "650e8400-e29b-41d4-a716-446655440000",
  "name": "Working Hours",
  "isDefault": true,
  "timezone": "America/New_York",
  "schedule": [...],
  "eventTypeCount": 3,
  "createdAt": "2025-01-15T10:30:00+00:00",
  "updatedAt": "2025-01-15T10:30:00+00:00"
}
```

**Error Response (404 Not Found):**

```json
{
  "error": "NOT_FOUND",
  "message": "Availability schedule not found",
  "statusCode": 404,
  "timestamp": "2025-01-15T10:30:00+00:00"
}
```

---

### 4. Update Availability Schedule

**Endpoint:** `PUT /availability-schedules/{id}`
**Authentication:** Required

Update an existing availability schedule. All fields are optional.

**Path Parameters:**
- `id` (string, UUID): Availability schedule ID

**Request Body:**

```json
{
  "name": "Updated Working Hours",
  "timezone": "America/Los_Angeles",
  "schedule": [...]
}
```

**Request Example:**

```bash
curl -X PUT "http://localhost:8080/api/availability-schedules/550e8400-e29b-41d4-a716-446655440000" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Working Hours",
    "timezone": "America/Los_Angeles"
  }'
```

**Response (200 OK):**

```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "userId": "650e8400-e29b-41d4-a716-446655440000",
  "name": "Updated Working Hours",
  "isDefault": true,
  "timezone": "America/Los_Angeles",
  "schedule": [...],
  "eventTypeCount": 3,
  "createdAt": "2025-01-15T10:30:00+00:00",
  "updatedAt": "2025-01-15T15:45:00+00:00"
}
```

---

### 5. Delete Availability Schedule

**Endpoint:** `DELETE /availability-schedules/{id}`
**Authentication:** Required

Delete an availability schedule. Cannot delete default schedules or schedules in use by event types.

**Path Parameters:**
- `id` (string, UUID): Availability schedule ID

**Request Example:**

```bash
curl -X DELETE "http://localhost:8080/api/availability-schedules/550e8400-e29b-41d4-a716-446655440000" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

**Response (200 OK):**

```json
{
  "message": "Availability schedule deleted successfully"
}
```

**Error Response (409 Conflict):**

```json
{
  "error": "CONFLICT",
  "message": "Cannot delete the default availability schedule",
  "statusCode": 409,
  "timestamp": "2025-01-15T10:30:00+00:00"
}
```

---

### 6. Duplicate Availability Schedule

**Endpoint:** `POST /availability-schedules/{id}/duplicate`
**Authentication:** Required

Create a copy of an existing availability schedule.

**Path Parameters:**
- `id` (string, UUID): Availability schedule ID to duplicate

**Request Example:**

```bash
curl -X POST "http://localhost:8080/api/availability-schedules/550e8400-e29b-41d4-a716-446655440000/duplicate" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

**Response (201 Created):**

```json
{
  "id": "760e8400-e29b-41d4-a716-446655440000",
  "userId": "650e8400-e29b-41d4-a716-446655440000",
  "name": "Working Hours (Copy)",
  "isDefault": false,
  "timezone": "America/New_York",
  "schedule": [...],
  "eventTypeCount": 0,
  "createdAt": "2025-01-15T16:00:00+00:00",
  "updatedAt": "2025-01-15T16:00:00+00:00"
}
```

---

### 7. Set Default Availability Schedule

**Endpoint:** `PATCH /availability-schedules/{id}/set-default`
**Authentication:** Required

Set an availability schedule as the default. Automatically unsets any other default schedules.

**Path Parameters:**
- `id` (string, UUID): Availability schedule ID

**Request Example:**

```bash
curl -X PATCH "http://localhost:8080/api/availability-schedules/550e8400-e29b-41d4-a716-446655440000/set-default" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

**Response (200 OK):**

```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "userId": "650e8400-e29b-41d4-a716-446655440000",
  "name": "Working Hours",
  "isDefault": true,
  "timezone": "America/New_York",
  "schedule": [...],
  "eventTypeCount": 3,
  "createdAt": "2025-01-15T10:30:00+00:00",
  "updatedAt": "2025-01-15T16:15:00+00:00"
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
| 400 | Bad Request - Invalid input data |
| 401 | Unauthorized - Invalid or missing authentication token |
| 404 | Not Found - Resource does not exist |
| 409 | Conflict - Cannot perform action due to conflict (e.g., deleting default schedule) |
| 422 | Validation Error - Input validation failed |
| 500 | Internal Server Error - Server-side error occurred |

### Error Codes

| Error Code | Description |
|------------|-------------|
| `UNAUTHORIZED` | Authentication token is invalid or missing |
| `NOT_FOUND` | Requested availability schedule not found |
| `VALIDATION_ERROR` | Input validation failed |
| `CONFLICT` | Cannot perform action (e.g., delete default schedule) |
| `INTERNAL_ERROR` | An unexpected error occurred on the server |

---

## Code Examples

### React Example

```javascript
import { useState, useEffect } from 'react';
import api from './api';

function AvailabilitySchedules() {
  const [schedules, setSchedules] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Fetch all schedules
  useEffect(() => {
    const fetchSchedules = async () => {
      try {
        setLoading(true);
        const response = await api.get('/availability-schedules');
        setSchedules(response.data.schedules);
      } catch (err) {
        setError(err.response?.data?.message || 'Failed to fetch schedules');
      } finally {
        setLoading(false);
      }
    };

    fetchSchedules();
  }, []);

  // Create new schedule
  const createSchedule = async (scheduleData) => {
    try {
      const response = await api.post('/availability-schedules', scheduleData);
      setSchedules([...schedules, response.data]);
      return response.data;
    } catch (err) {
      throw new Error(err.response?.data?.message || 'Failed to create schedule');
    }
  };

  // Update schedule
  const updateSchedule = async (id, updates) => {
    try {
      const response = await api.put(`/availability-schedules/${id}`, updates);
      setSchedules(schedules.map(s => s.id === id ? response.data : s));
      return response.data;
    } catch (err) {
      throw new Error(err.response?.data?.message || 'Failed to update schedule');
    }
  };

  // Delete schedule
  const deleteSchedule = async (id) => {
    try {
      await api.delete(`/availability-schedules/${id}`);
      setSchedules(schedules.filter(s => s.id !== id));
    } catch (err) {
      if (err.response?.status === 409) {
        throw new Error('Cannot delete default schedule or schedule in use');
      }
      throw new Error(err.response?.data?.message || 'Failed to delete schedule');
    }
  };

  // Duplicate schedule
  const duplicateSchedule = async (id) => {
    try {
      const response = await api.post(`/availability-schedules/${id}/duplicate`);
      setSchedules([...schedules, response.data]);
      return response.data;
    } catch (err) {
      throw new Error(err.response?.data?.message || 'Failed to duplicate schedule');
    }
  };

  // Set as default
  const setDefault = async (id) => {
    try {
      const response = await api.patch(`/availability-schedules/${id}/set-default`);
      setSchedules(schedules.map(s => ({
        ...s,
        isDefault: s.id === id
      })));
      return response.data;
    } catch (err) {
      throw new Error(err.response?.data?.message || 'Failed to set default schedule');
    }
  };

  if (loading) return <div>Loading schedules...</div>;
  if (error) return <div>Error: {error}</div>;

  return (
    <div>
      <h1>Availability Schedules</h1>
      {schedules.map(schedule => (
        <div key={schedule.id}>
          <h3>{schedule.name} {schedule.isDefault && '(Default)'}</h3>
          <p>Timezone: {schedule.timezone}</p>
          <p>Event Types: {schedule.eventTypeCount}</p>
          <button onClick={() => setDefault(schedule.id)}>Set as Default</button>
          <button onClick={() => duplicateSchedule(schedule.id)}>Duplicate</button>
          <button onClick={() => deleteSchedule(schedule.id)}>Delete</button>
        </div>
      ))}
    </div>
  );
}

export default AvailabilitySchedules;
```

### Vue.js Example (Composition API)

```javascript
<template>
  <div>
    <h1>Availability Schedules</h1>
    <div v-if="loading">Loading schedules...</div>
    <div v-else-if="error">Error: {{ error }}</div>
    <div v-else>
      <div v-for="schedule in schedules" :key="schedule.id">
        <h3>{{ schedule.name }} <span v-if="schedule.isDefault">(Default)</span></h3>
        <p>Timezone: {{ schedule.timezone }}</p>
        <p>Event Types: {{ schedule.eventTypeCount }}</p>
        <button @click="setDefault(schedule.id)">Set as Default</button>
        <button @click="duplicateSchedule(schedule.id)">Duplicate</button>
        <button @click="deleteSchedule(schedule.id)">Delete</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from './api';

const schedules = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchSchedules = async () => {
  try {
    loading.value = true;
    const response = await api.get('/availability-schedules');
    schedules.value = response.data.schedules;
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to fetch schedules';
  } finally {
    loading.value = false;
  }
};

const createSchedule = async (scheduleData) => {
  try {
    const response = await api.post('/availability-schedules', scheduleData);
    schedules.value.push(response.data);
    return response.data;
  } catch (err) {
    throw new Error(err.response?.data?.message || 'Failed to create schedule');
  }
};

const updateSchedule = async (id, updates) => {
  try {
    const response = await api.put(`/availability-schedules/${id}`, updates);
    const index = schedules.value.findIndex(s => s.id === id);
    if (index !== -1) {
      schedules.value[index] = response.data;
    }
    return response.data;
  } catch (err) {
    throw new Error(err.response?.data?.message || 'Failed to update schedule');
  }
};

const deleteSchedule = async (id) => {
  try {
    await api.delete(`/availability-schedules/${id}`);
    schedules.value = schedules.value.filter(s => s.id !== id);
  } catch (err) {
    if (err.response?.status === 409) {
      throw new Error('Cannot delete default schedule or schedule in use');
    }
    throw new Error(err.response?.data?.message || 'Failed to delete schedule');
  }
};

const duplicateSchedule = async (id) => {
  try {
    const response = await api.post(`/availability-schedules/${id}/duplicate`);
    schedules.value.push(response.data);
    return response.data;
  } catch (err) {
    throw new Error(err.response?.data?.message || 'Failed to duplicate schedule');
  }
};

const setDefault = async (id) => {
  try {
    const response = await api.patch(`/availability-schedules/${id}/set-default`);
    schedules.value = schedules.value.map(s => ({
      ...s,
      isDefault: s.id === id
    }));
    return response.data;
  } catch (err) {
    throw new Error(err.response?.data?.message || 'Failed to set default schedule');
  }
};

onMounted(() => {
  fetchSchedules();
});
</script>
```

### Vanilla JavaScript/TypeScript Example

```typescript
// types.ts
interface TimeSlot {
  start: string;
  end: string;
}

interface DaySchedule {
  day: 'Sunday' | 'Monday' | 'Tuesday' | 'Wednesday' | 'Thursday' | 'Friday' | 'Saturday';
  enabled: boolean;
  slots: TimeSlot[];
}

interface AvailabilitySchedule {
  id: string;
  userId: string;
  name: string;
  isDefault: boolean;
  timezone: string;
  schedule: DaySchedule[];
  eventTypeCount: number;
  createdAt: string;
  updatedAt: string;
}

interface CreateScheduleRequest {
  name: string;
  isDefault?: boolean;
  timezone: string;
  schedule: DaySchedule[];
}

// api.ts
class AvailabilitySchedulesAPI {
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

  async getSchedules(params?: {
    isDefault?: boolean;
    limit?: number;
    offset?: number;
  }): Promise<{
    schedules: AvailabilitySchedule[];
    total: number;
    limit: number;
    offset: number;
  }> {
    const queryParams = new URLSearchParams();
    if (params?.isDefault !== undefined) {
      queryParams.append('isDefault', String(params.isDefault));
    }
    if (params?.limit) {
      queryParams.append('limit', String(params.limit));
    }
    if (params?.offset) {
      queryParams.append('offset', String(params.offset));
    }

    const query = queryParams.toString();
    return this.request(`/availability-schedules${query ? `?${query}` : ''}`);
  }

  async createSchedule(data: CreateScheduleRequest): Promise<AvailabilitySchedule> {
    return this.request('/availability-schedules', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async getScheduleById(id: string): Promise<AvailabilitySchedule> {
    return this.request(`/availability-schedules/${id}`);
  }

  async updateSchedule(
    id: string,
    updates: Partial<CreateScheduleRequest>
  ): Promise<AvailabilitySchedule> {
    return this.request(`/availability-schedules/${id}`, {
      method: 'PUT',
      body: JSON.stringify(updates),
    });
  }

  async deleteSchedule(id: string): Promise<{ message: string }> {
    return this.request(`/availability-schedules/${id}`, {
      method: 'DELETE',
    });
  }

  async duplicateSchedule(id: string): Promise<AvailabilitySchedule> {
    return this.request(`/availability-schedules/${id}/duplicate`, {
      method: 'POST',
    });
  }

  async setDefault(id: string): Promise<AvailabilitySchedule> {
    return this.request(`/availability-schedules/${id}/set-default`, {
      method: 'PATCH',
    });
  }
}

// Usage
const api = new AvailabilitySchedulesAPI('http://localhost:8080/api');

// Fetch schedules
const { schedules } = await api.getSchedules({ limit: 10 });

// Create schedule
const newSchedule = await api.createSchedule({
  name: 'Working Hours',
  timezone: 'America/New_York',
  schedule: [/* ... */],
});

// Update schedule
const updated = await api.updateSchedule(newSchedule.id, {
  name: 'Updated Working Hours',
});

// Delete schedule
await api.deleteSchedule(newSchedule.id);
```

---

## Best Practices

### 1. Token Storage

Store authentication tokens securely:

```javascript
// Good: Use httpOnly cookies (if supported by backend)
// Better: Use secure localStorage with XSS protection
localStorage.setItem('authToken', token);

// Never: Store in plain JavaScript variables accessible globally
window.authToken = token; // Bad!
```

### 2. Request/Response Handling

Always handle both success and error cases:

```javascript
try {
  const response = await api.get('/availability-schedules');
  // Handle success
  setSchedules(response.data.schedules);
} catch (error) {
  // Handle specific error codes
  if (error.response?.status === 401) {
    // Redirect to login
  } else if (error.response?.status === 409) {
    // Show conflict error to user
  } else {
    // Show generic error
  }
}
```

### 3. Error Management

Display user-friendly error messages:

```javascript
const getErrorMessage = (error) => {
  const statusCode = error.response?.status;
  const errorData = error.response?.data;

  switch (statusCode) {
    case 401:
      return 'Please log in to continue';
    case 404:
      return 'Schedule not found';
    case 409:
      return 'Cannot delete default schedule or schedule in use';
    case 422:
      return errorData?.message || 'Invalid input data';
    default:
      return 'An unexpected error occurred';
  }
};
```

### 4. Schedule Validation

Validate schedule data before sending to API:

```javascript
const validateSchedule = (schedule) => {
  // Must have exactly 7 days
  if (schedule.length !== 7) {
    throw new Error('Schedule must contain exactly 7 days');
  }

  // Check each day
  schedule.forEach((day, index) => {
    // Validate day names
    const validDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    if (!validDays.includes(day.day)) {
      throw new Error(`Invalid day: ${day.day}`);
    }

    // Validate time slots
    day.slots.forEach((slot) => {
      const timeRegex = /^([0-1][0-9]|2[0-3]):[0-5][0-9]$/;
      if (!timeRegex.test(slot.start) || !timeRegex.test(slot.end)) {
        throw new Error('Time must be in HH:MM format (24-hour)');
      }

      // Check start < end
      if (slot.start >= slot.end) {
        throw new Error('Start time must be before end time');
      }
    });
  });

  return true;
};
```

### 5. Timezone Handling

Always use IANA timezone identifiers:

```javascript
// Good
const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
// e.g., "America/New_York", "Europe/London"

// Bad
const timezone = "EST"; // Don't use abbreviations
```

---

## Testing Checklist

### Endpoint Testing

- [ ] Get all schedules - success case
- [ ] Get all schedules - with filters (isDefault)
- [ ] Get all schedules - with pagination
- [ ] Create schedule - success case
- [ ] Create schedule - validation errors
- [ ] Get schedule by ID - success case
- [ ] Get schedule by ID - not found
- [ ] Update schedule - success case
- [ ] Update schedule - partial update
- [ ] Delete schedule - success case
- [ ] Delete schedule - conflict (default schedule)
- [ ] Delete schedule - conflict (in use by event types)
- [ ] Duplicate schedule - success case
- [ ] Set default schedule - success case

### Error Scenarios

- [ ] Unauthorized access (missing token)
- [ ] Unauthorized access (invalid token)
- [ ] Invalid schedule data (missing required fields)
- [ ] Invalid schedule data (wrong format)
- [ ] Invalid time slots (start >= end)
- [ ] Schedule array with wrong number of days
- [ ] Invalid timezone identifier

### Integration Verification

- [ ] Token refresh on expiration
- [ ] Proper error handling for all status codes
- [ ] Loading states during API calls
- [ ] Optimistic UI updates
- [ ] Rollback on error

---

## Additional Resources

- **Timezone Database:** [IANA Time Zone Database](https://www.iana.org/time-zones)
- **HTTP Status Codes:** [MDN Web Docs](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status)
- **Laravel Sanctum:** [Official Documentation](https://laravel.com/docs/sanctum)

---

**Generated:** 2025-11-11
**API Version:** 1.0.0
**Last Updated:** 2025-11-11
