<x-guest-layout>
    <!-- Error Popup Modal -->
    <div id="errorModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900">Registration Error</h3>
                </div>
            </div>
            <div class="mb-4">
                <p id="errorMessage" class="text-sm text-gray-600"></p>
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="closeErrorModal()" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Success Popup Modal -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900">Registration Successful</h3>
                </div>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-600">Your registration is pending approval by an admin. You will be notified once approved.</p>
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="closeSuccessModal()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                    Continue to Login
                </button>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" id="registerForm">
        @csrf

        <!-- First Name -->
        <div class="form-group">
            <label for="firstname" class="form-label">First Name</label>
            <input id="firstname" class="form-input" type="text" name="firstname" value="{{ old('firstname') }}" required autofocus autocomplete="firstname" />
            <x-input-error :messages="$errors->get('firstname')" class="mt-2" />
        </div>

        <!-- Middle Name -->
        <div class="form-group">
            <label for="middlename" class="form-label">Middle Name</label>
            <input id="middlename" class="form-input" type="text" name="middlename" value="{{ old('middlename') }}" />
            <x-input-error :messages="$errors->get('middlename')" class="mt-2" />
        </div>

        <!-- Last Name -->
        <div class="form-group">
            <label for="lastname" class="form-label">Last Name</label>
            <input id="lastname" class="form-input" type="text" name="lastname" value="{{ old('lastname') }}" required autocomplete="lastname" />
            <x-input-error :messages="$errors->get('lastname')" class="mt-2" />
        </div>

        <!-- Gender -->
        <div class="form-group">
            <label for="gender" class="form-label">Gender</label>
            <select id="gender" name="gender" class="form-input" required>
                <option value="">Select gender</option>
                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
            </select>
            <x-input-error :messages="$errors->get('gender')" class="mt-2" />
        </div>

        <!-- Birthdate -->
        <div class="form-group">
            <label for="bdate" class="form-label">Birthdate</label>
            <input id="bdate" class="form-input" type="date" name="bdate" value="{{ old('bdate') }}" required />
            <x-input-error :messages="$errors->get('bdate')" class="mt-2" />
        </div>

        <!-- Address (Cebu, Philippines only) with suggestions -->
        <div class="form-group">
            <label for="address" class="form-label">Address (Cebu, Philippines only)</label>
            <input id="address" class="form-input" type="text" name="address" list="address_suggestions" value="{{ old('address') }}" required />
            <datalist id="address_suggestions"></datalist>
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        <!-- Username -->
        <div class="form-group">
            <label for="username" class="form-label">Username</label>
            <input id="username" class="form-input" type="text" name="username" value="{{ old('username') }}" required autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input id="password" class="form-input" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input id="password_confirmation" class="form-input" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Profile Picture -->
        <div class="form-group">
            <label for="photo" class="form-label">Profile Picture</label>
            <input id="photo" type="file" name="photo" accept="image/*" class="form-input">
            <x-input-error :messages="$errors->get('photo')" class="mt-2" />
        </div>

        <!-- Skill Category (for filtering only, not submitted) -->
        <div class="form-group">
            <label for="skill_category" class="form-label">Skill Category</label>
            <select id="skill_category" class="form-input" required>
                <option value="">Select a category</option>
                @foreach($skills->groupBy('category') as $category => $group)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select>
        </div>

        <!-- Skill Name (this is submitted) -->
        <div class="form-group">
            <label for="skill_id" class="form-label">Skill Name</label>
            <select id="skill_id" name="skill_id" class="form-input" required>
                <option value="">Select a skill</option>
                @foreach($skills as $skill)
                    <option value="{{ $skill->skill_id }}" data-category="{{ $skill->category }}">{{ $skill->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('skill_id')" class="mt-2" />
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('skill_category');
            const skillSelect = document.getElementById('skill_id');
            const allOptions = Array.from(skillSelect.options);

            categorySelect.addEventListener('change', function() {
                const selectedCategory = this.value;
                skillSelect.innerHTML = '<option value="">Select a skill</option>';
                allOptions.forEach(option => {
                    if (!option.value) return; // skip placeholder
                    if (option.getAttribute('data-category') === selectedCategory) {
                        skillSelect.appendChild(option.cloneNode(true));
                    }
                });
            });

            // Address suggestions (Cebu only)
            const addressInput = document.getElementById('address');
            const dataList = document.getElementById('address_suggestions');

            function debounce(fn, delay) {
                let t;
                return function(...args) {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, args), delay);
                };
            }

            async function fetchSuggestions(query) {
                try {
                    const url = '/api/addresses/cebu/suggest?q=' + encodeURIComponent(query || '');
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();
                    dataList.innerHTML = '';
                    (data.suggestions || []).forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item;
                        dataList.appendChild(opt);
                    });
                } catch (e) {}
            }

            addressInput.addEventListener('input', debounce(function(e) {
                fetchSuggestions(e.target.value);
            }, 250));

            // Preload some suggestions initially
            fetchSuggestions('');

            // Form submission with error handling
            const form = document.getElementById('registerForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Registering...';
                submitBtn.disabled = true;

                // Create FormData
                const formData = new FormData(form);

                // Submit form via fetch
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (response.ok) {
                        return response.text();
                    } else {
                        return response.json().then(data => {
                            throw new Error(data.message || 'Registration failed');
                        });
                    }
                })
                .then(data => {
                    // Check if response contains error messages
                    if (data.includes('error') || data.includes('Error')) {
                        showErrorModal('Registration failed. Please check your information and try again.');
                    } else {
                        showSuccessModal();
                    }
                })
                .catch(error => {
                    showErrorModal(error.message || 'An error occurred during registration. Please try again.');
                })
                .finally(() => {
                    // Reset button state
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
            });

            // Real-time validation
            const inputs = form.querySelectorAll('input[required], select[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });
            });

            function validateField(field) {
                const value = field.value.trim();
                const fieldName = field.name;
                let isValid = true;
                let errorMessage = '';

                // Remove existing error styling
                field.classList.remove('border-red-500', 'border-green-500');
                const existingError = field.parentNode.querySelector('.field-error');
                if (existingError) {
                    existingError.remove();
                }

                // Validation rules
                if (field.hasAttribute('required') && !value) {
                    isValid = false;
                    errorMessage = `${fieldName.charAt(0).toUpperCase() + fieldName.slice(1)} is required.`;
                } else if (fieldName === 'email' && value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address.';
                    }
                } else if (fieldName === 'password' && value) {
                    if (value.length < 8) {
                        isValid = false;
                        errorMessage = 'Password must be at least 8 characters long.';
                    }
                } else if (fieldName === 'password_confirmation' && value) {
                    const password = document.getElementById('password').value;
                    if (value !== password) {
                        isValid = false;
                        errorMessage = 'Passwords do not match.';
                    }
                } else if (fieldName === 'username' && value) {
                    if (value.length < 3) {
                        isValid = false;
                        errorMessage = 'Username must be at least 3 characters long.';
                    }
                }

                // Apply styling and show error
                if (!isValid) {
                    field.classList.add('border-red-500');
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'field-error text-red-600 text-sm mt-1';
                    errorDiv.textContent = errorMessage;
                    field.parentNode.appendChild(errorDiv);
                } else if (value) {
                    field.classList.add('border-green-500');
                }
            }
        });

        function showErrorModal(message) {
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorModal').classList.remove('hidden');
        }

        function closeErrorModal() {
            document.getElementById('errorModal').classList.add('hidden');
        }

        function showSuccessModal() {
            document.getElementById('successModal').classList.remove('hidden');
        }

        function closeSuccessModal() {
            document.getElementById('successModal').classList.add('hidden');
            window.location.href = '{{ route("login") }}';
        }

        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.id === 'errorModal') {
                closeErrorModal();
            }
            if (e.target.id === 'successModal') {
                closeSuccessModal();
            }
        });
        </script>

        <div class="form-footer">
            <a href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <button type="submit" class="btn-primary">
                {{ __('REGISTER') }}
            </button>
        </div>
    </form>
</x-guest-layout>
