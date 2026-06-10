// isurf-api.js
const iSurfAPI = {
    baseUrl: typeof apiBaseUrl !== 'undefined' ? apiBaseUrl : 'http://localhost:8000/api',

    async getLatestReadings() {
        try {
            const response = await fetch(`${this.baseUrl}/readings/latest`);
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error('Error fetching latest readings:', error);
            return null;
        }
    },

    async deleteDevice(deviceId) {
        try {
            const response = await fetch(`${this.baseUrl}/devices/${deviceId}`, {
                method: 'DELETE'
            });
            if (!response.ok) throw new Error('Failed to delete device');
            return true;
        } catch (error) {
            console.error('Error deleting device:', error);
            throw error;
        }
    },

    formatDateTimeWithTZ(dateString) {
        if (!dateString) return 'Never';
        const date = new Date(dateString);
        if (isNaN(date)) return 'Invalid Date';
        
        const offset = -date.getTimezoneOffset();
        const sign = offset >= 0 ? '+' : '-';
        const offsetHours = String(Math.floor(Math.abs(offset) / 60)).padStart(2, '0');
        const offsetMins = String(Math.abs(offset) % 60).padStart(2, '0');
        const tz = `UTC${sign}${offsetHours}:${offsetMins}`;
        
        return `${date.toLocaleString('id-ID')} (${tz})`;
    },

    formatTimeWithTZ(dateString) {
        if (!dateString) return 'Never';
        const date = typeof dateString === 'string' ? new Date(dateString) : dateString;
        if (isNaN(date)) return 'Invalid Date';
        
        const offset = -date.getTimezoneOffset();
        const sign = offset >= 0 ? '+' : '-';
        const offsetHours = String(Math.floor(Math.abs(offset) / 60)).padStart(2, '0');
        const offsetMins = String(Math.abs(offset) % 60).padStart(2, '0');
        const tz = `UTC${sign}${offsetHours}`;
        
        return `${date.toLocaleTimeString('id-ID')} (${tz})`;
    },

    async getDevices() {
        try {
            const response = await fetch(`${this.baseUrl}/devices`);
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error('Error fetching devices:', error);
            return [];
        }
    },

    async addDevice(deviceData) {
        try {
            const response = await fetch(`${this.baseUrl}/devices/`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(deviceData)
            });
            
            if (!response.ok) {
                const errData = await response.json();
                throw new Error(errData.detail || 'Failed to add device');
            }
            return await response.json();
        } catch (error) {
            console.error('Error adding device:', error);
            throw error;
        }
    },

    async getDeviceSensors(deviceId) {
        try {
            const response = await fetch(`${this.baseUrl}/devices/${deviceId}/sensors`);
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            return {
                id: deviceId,
                name: 'Device',
                sensors: data
            };
        } catch (error) {
            console.error('Error fetching device sensors:', error);
            return null;
        }
    },

    async updateSensorThreshold(deviceId, sensorId, minThreshold, maxThreshold) {
        try {
            const response = await fetch(`${this.baseUrl}/devices/${deviceId}/sensors/${sensorId}/thresholds`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    min_threshold: minThreshold !== "" ? parseFloat(minThreshold) : null,
                    max_threshold: maxThreshold !== "" ? parseFloat(maxThreshold) : null
                })
            });
            if (!response.ok) throw new Error('Failed to update threshold');
            return await response.json();
        } catch (error) {
            console.error('Error updating threshold:', error);
            throw error;
        }
    },

    async triggerManualPump(deviceId, action, durationMinutes) {
        try {
            const response = await fetch(`${this.baseUrl}/irrigation/trigger`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ device_id: deviceId, action: action, duration_minutes: durationMinutes })
            });
            if (!response.ok) throw new Error('Failed to trigger pump');
            return await response.json();
        } catch (error) {
            console.error('Error triggering pump:', error);
            throw error;
        }
    },

    async getSchedules(deviceId = null) {
        try {
            const url = deviceId ? `${this.baseUrl}/irrigation/schedules?device_id=${deviceId}` : `${this.baseUrl}/irrigation/schedules`;
            const response = await fetch(url);
            if (!response.ok) throw new Error('Failed to fetch schedules');
            return await response.json();
        } catch (error) {
            console.error('Error fetching schedules:', error);
            return [];
        }
    },

    async addSchedule(scheduleData) {
        try {
            const response = await fetch(`${this.baseUrl}/irrigation/schedules`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(scheduleData)
            });
            if (!response.ok) throw new Error('Failed to add schedule');
            return await response.json();
        } catch (error) {
            console.error('Error adding schedule:', error);
            throw error;
        }
    },

    async deleteSchedule(scheduleId) {
        try {
            const response = await fetch(`${this.baseUrl}/irrigation/schedules/${scheduleId}`, {
                method: 'DELETE'
            });
            if (!response.ok) throw new Error('Failed to delete schedule');
            return await response.json();
        } catch (error) {
            console.error('Error deleting schedule:', error);
            throw error;
        }
    }
};
