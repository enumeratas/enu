(function () {
    function loadFlatpickr(done) {
        if (window.flatpickr) {
            done();
            return;
        }
        if (!document.querySelector('link[data-bis-flatpickr]')) {
            var css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css';
            css.setAttribute('data-bis-flatpickr', '1');
            document.head.appendChild(css);
        }
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/flatpickr';
        script.onload = function () {
            done();
        };
        script.onerror = function () {
            done(new Error('flatpickr'));
        };
        document.head.appendChild(script);
    }

    function fillTimes(timeSelect, times, keep) {
        timeSelect.innerHTML = '';
        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = times.length ? 'Select a time' : 'Select a date first';
        timeSelect.appendChild(placeholder);
        times.forEach(function (slot) {
            var option = document.createElement('option');
            option.value = slot.value;
            option.textContent = slot.available ? slot.label : slot.label + ' — booked';
            option.disabled = !slot.available;
            if (slot.available && slot.value === keep) {
                option.selected = true;
            }
            timeSelect.appendChild(option);
        });
        timeSelect.disabled = times.length === 0;
    }

    function fillEndTimes(endSelect, times, startValue, keep) {
        if (!endSelect) {
            return;
        }
        var keepValue = keep || endSelect.getAttribute('data-current') || endSelect.value || '';
        endSelect.innerHTML = '';
        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = times.length ? 'Select an end time' : 'Select a date first';
        endSelect.appendChild(placeholder);
        var added = 0;
        times.forEach(function (slot) {
            if (startValue && slot.value <= startValue) {
                return;
            }
            var option = document.createElement('option');
            option.value = slot.value;
            option.textContent = slot.available ? slot.label : slot.label + ' — booked';
            option.disabled = !slot.available;
            if (slot.available && slot.value === keepValue) {
                option.selected = true;
            }
            endSelect.appendChild(option);
            added += 1;
        });
        endSelect.disabled = added === 0;
        endSelect.removeAttribute('data-current');
    }

    function bind(dateInput, timeSelect, options) {
        if (!dateInput || !timeSelect) {
            return;
        }
        options = options || {};
        var endpoint = options.endpoint || '/public/concern/slots';
        var excludeScheduleId = options.excludeScheduleId || 0;
        var excludeConcernId = options.excludeConcernId || 0;
        var message = options.message || null;
        var endSelect = options.endTimeSelect || null;
        var lastTimes = [];

        function params(extra) {
            var query = new URLSearchParams(extra || {});
            if (excludeScheduleId) {
                query.set('exclude_schedule_id', String(excludeScheduleId));
            }
            if (excludeConcernId) {
                query.set('exclude_concern_id', String(excludeConcernId));
            }
            return query.toString();
        }

        function setMessage(text, color) {
            if (!message) {
                return;
            }
            message.textContent = text || '';
            message.style.color = color || '#9aa0b4';
        }

        function loadTimes(date) {
            if (!date) {
                lastTimes = [];
                fillTimes(timeSelect, [], '');
                fillEndTimes(endSelect, [], '', '');
                setMessage('');
                return;
            }
            setMessage('Checking open times...', '#9aa0b4');
            fetch(endpoint + '?' + params({ date: date }), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store'
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    var times = data.times || [];
                    lastTimes = times;
                    var keep = timeSelect.getAttribute('data-current') || '';
                    fillTimes(timeSelect, times, keep);
                    timeSelect.removeAttribute('data-current');
                    fillEndTimes(endSelect, times, timeSelect.value, endSelect ? endSelect.getAttribute('data-current') : '');
                    var open = times.some(function (slot) { return slot.available; });
                    var taken = times.some(function (slot) { return !slot.available; });
                    if (!open) {
                        setMessage('This date is fully booked. Choose another date.', '#c0392b');
                    } else if (taken) {
                        setMessage('Taken hours are marked booked.', '#1a7a55');
                    } else {
                        setMessage('');
                    }
                })
                .catch(function () {
                    setMessage('Times could not be loaded. Try the date again.', '#c0392b');
                });
        }

        if (endSelect) {
            timeSelect.addEventListener('change', function () {
                fillEndTimes(endSelect, lastTimes, timeSelect.value, endSelect.value);
            });
        }

        function attachPicker(booked) {
            loadFlatpickr(function (error) {
                if (error || typeof window.flatpickr !== 'function') {
                    dateInput.addEventListener('change', function () {
                        loadTimes(dateInput.value);
                    });
                    if (dateInput.value) {
                        loadTimes(dateInput.value);
                    }
                    return;
                }
                if (dateInput._flatpickr) {
                    dateInput._flatpickr.destroy();
                }
                window.flatpickr(dateInput, {
                    dateFormat: 'Y-m-d',
                    minDate: Object.prototype.hasOwnProperty.call(options, 'minDate') ? (options.minDate || undefined) : 'today',
                    disableMobile: true,
                    disable: [
                        function (date) { return date.getDay() === 0; }
                    ].concat(booked || []),
                    onChange: function (_selected, dateStr) {
                        loadTimes(dateStr);
                    }
                });
                if (dateInput.value) {
                    loadTimes(dateInput.value);
                }
            });
        }

        fetch(endpoint + (params() ? '?' + params() : ''), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            cache: 'no-store'
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                attachPicker(data.dates || []);
            })
            .catch(function () {
                attachPicker([]);
            });
    }

    window.BISAppointmentSlots = { bind: bind };
})();
