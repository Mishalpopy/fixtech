# Setup Instructions for Fixtek APIs

## ✅ Implementation Complete!

All API endpoints for Customer and Partner modules have been successfully implemented and are ready to use.

## Quick Setup

### 1. Verify Installation

All necessary files have been created. The following components are in place:

**✅ API Controllers:**
- Customer Authentication Controller
- Partner Authentication Controller

**✅ Validation Requests:**
- Customer Registration & Login validators
- Partner Registration & Login validators

**✅ Routes:**
- API routes configured in `routes/api.php`
- Routes properly registered in `bootstrap/app.php`

**✅ Models Updated:**
- Customer model with API token support
- Partner model with API token support

**✅ Configuration:**
- Sanctum configured for multiple guards

### 2. Start Using the API

#### Option 1: Using Postman (Recommended)

1. **Open Postman**

2. **Import Collection:**
   - Click "Import"
   - Select `Fixtek_API_Collection.postman_collection.json`

3. **Import Environment:**
   - Go to Environments
   - Click "Import"
   - Select `Fixtek_API_Environment.postman_environment.json`

4. **Select Environment:**
   - Click the environment dropdown (top right)
   - Select "Fixtek API Environment"

5. **Start Testing:**
   - Health Check
   - Customer Registration
   - Customer Login
   - Customer Profile (uses auto-saved token)
   - Partner Registration
   - Partner Login
   - Partner Profile (uses auto-saved token)

#### Option 2: Using cURL

Start the Laravel development server:
```bash
php artisan serve
```

Test the health check:
```bash
curl http://localhost:8000/api/health
```

Register a customer:
```bash
curl -X POST http://localhost:8000/api/v1/customer/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+971501234567",
    "address": "123 Main St"
  }'
```

## Available API Endpoints

### Customer Endpoints
| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| POST | `/api/v1/customer/register` | No | Register new customer |
| POST | `/api/v1/customer/login` | No | Login customer |
| GET | `/api/v1/customer/profile` | Yes | Get customer profile |
| POST | `/api/v1/customer/logout` | Yes | Logout customer |

### Partner Endpoints
| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| POST | `/api/v1/partner/register` | No | Register new partner |
| POST | `/api/v1/partner/login` | No | Login partner |
| GET | `/api/v1/partner/profile` | Yes | Get partner profile |
| POST | `/api/v1/partner/logout` | Yes | Logout partner |

### Utility Endpoints
| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| GET | `/api/health` | No | Check API status |

## Verification

### Check Routes are Loaded
```bash
php artisan route:list --path=api
```

You should see 9 routes listed.

### Expected Output
```
  GET|HEAD   api/health
  POST       api/v1/customer/login
  POST       api/v1/customer/logout
  GET|HEAD   api/v1/customer/profile
  POST       api/v1/customer/register
  POST       api/v1/partner/login
  POST       api/v1/partner/logout
  GET|HEAD   api/v1/partner/profile
  POST       api/v1/partner/register
```

## Testing Workflow

### Customer Flow
1. ✅ Register a new customer → Receive token
2. ✅ View customer profile (with token)
3. ✅ Logout (revokes token)
4. ✅ Login again → Receive new token

### Partner Flow
1. ✅ Register a new partner → Receive token (status: pending)
2. ✅ View partner profile (with token)
3. ✅ Admin approves partner (via admin panel)
4. ✅ Partner has full access

## Authentication

All protected endpoints require Bearer token authentication:

```
Authorization: Bearer {your-token-here}
```

Tokens are automatically returned in the response after login or registration.

## Documentation Files

📚 **Available Documentation:**

1. **`API_DOCUMENTATION.md`**
   - Complete API reference
   - All endpoints documented
   - Request/response examples
   - Error codes and handling

2. **`API_QUICK_START.md`**
   - Quick start guide
   - cURL examples
   - Postman setup
   - Common issues and solutions

3. **`API_IMPLEMENTATION_SUMMARY.md`**
   - Technical overview
   - Implementation details
   - Architecture information
   - Future recommendations

4. **`SETUP_INSTRUCTIONS.md`** (this file)
   - Setup verification
   - Testing instructions
   - Quick reference

## Postman Files

📦 **Postman Collection Files:**

1. **`Fixtek_API_Collection.postman_collection.json`**
   - All API endpoints
   - Pre-configured requests
   - Auto-token management
   - Sample data

2. **`Fixtek_API_Environment.postman_environment.json`**
   - Environment variables
   - Base URL configuration
   - Token storage

## Important Notes

### Customer Registration
- ✅ Automatically activated
- ✅ Can login immediately
- ✅ No approval required

### Partner Registration
- ⏳ Approval status: pending
- ✅ Can login after registration
- ⏰ Requires admin approval for full access
- 📋 Includes company details

### Security Features
- 🔒 Password hashing (bcrypt)
- 🎟️ Token-based authentication
- 🔑 Token revocation on logout
- ✅ Status and approval checks
- 📝 Request validation

### Response Format
All API responses use JSON format with consistent structure:

**Success:**
```json
{
    "success": true,
    "message": "Success message",
    "data": { /* response data */ }
}
```

**Error:**
```json
{
    "success": false,
    "message": "Error message"
}
```

## Troubleshooting

### Routes Not Found
If routes are not showing up:
```bash
php artisan config:clear
php artisan route:clear
php artisan route:list --path=api
```

### Token Issues
If you get 401 Unauthenticated:
1. Verify token is in Authorization header
2. Check token format: `Bearer {token}`
3. Login again to get a new token

### Validation Errors
If you get 422 Validation Error:
1. Check all required fields are present
2. Verify password has min 8 characters
3. Ensure password_confirmation matches password
4. Verify email is unique

### Database Issues
If you get database errors:
```bash
php artisan migrate
```

## Next Steps

1. ✅ **Test API Endpoints**
   - Use Postman collection
   - Test all customer endpoints
   - Test all partner endpoints

2. ✅ **Read Documentation**
   - Review API_DOCUMENTATION.md
   - Check API_QUICK_START.md

3. ✅ **Integration**
   - Integrate with your frontend
   - Implement proper error handling
   - Add loading states

4. 📋 **Future Enhancements**
   - Add email verification
   - Implement password reset
   - Add profile update endpoints
   - Implement file upload for documents

## Support

For detailed information:
- See `API_DOCUMENTATION.md` for complete API reference
- See `API_QUICK_START.md` for quick examples
- See `API_IMPLEMENTATION_SUMMARY.md` for technical details

---

**Status:** ✅ Ready to Use  
**Version:** 1.0  
**Date:** October 22, 2025

Happy Testing! 🚀


