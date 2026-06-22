const ISURF_API = {
    baseUrl: '', // Local Yii2 routing

    getBaseUrl() {
        return (typeof window !== 'undefined' && window.appBaseUrl !== undefined) ? window.appBaseUrl : '';
    },

    async getLatestReadings() {
        try {
            const timestamp = new Date().getTime();
            const url = (typeof window !== 'undefined' && window.apiUrls) 
                ? `${window.apiUrls.latestReadings}&_t=${timestamp}`
                : `${this.getBaseUrl()}/index.php?r=site/latest-readings&_t=${timestamp}`;
            const response = await fetch(url);
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error('Error fetching latest readings:', error);
            return [];
        }
    },

    async getHistory(areaId, dataType, hours = 24) {
        try {
            const timestamp = new Date().getTime();
            const url = (typeof window !== 'undefined' && window.apiUrls)
                ? `${window.apiUrls.getHistory}&dataType=${encodeURIComponent(dataType)}&hours=${hours}&_t=${timestamp}`
                : `${this.getBaseUrl()}/index.php?r=site/get-history&dataType=${encodeURIComponent(dataType)}&hours=${hours}&_t=${timestamp}`;
            const response = await fetch(url);
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error('Error fetching history:', error);
            return [];
        }
    },

    async getAllLogs(hours = 24) {
        try {
            const timestamp = new Date().getTime();
            const url = (typeof window !== 'undefined' && window.apiUrls)
                ? `${window.apiUrls.getLogs}&hours=${hours}&_t=${timestamp}`
                : `${this.getBaseUrl()}/index.php?r=site/get-logs&hours=${hours}&_t=${timestamp}`;
            const response = await fetch(url);
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error('Error fetching logs:', error);
            return [];
        }
    },

    formatTimestamp(timestamp) {
        if (!timestamp) return '-';
        const date = new Date(timestamp + 'Z'); 
        const offset = -date.getTimezoneOffset();
        const sign = offset >= 0 ? '+' : '-';
        const offsetHours = String(Math.floor(Math.abs(offset) / 60)).padStart(2, '0');
        const tz = `UTC${sign}${offsetHours}`;
        
        return `${date.toLocaleTimeString('id-ID')} (${tz})`;
    },

    async getAreas() {
        try {
            const response = await fetch(`${this.baseUrl}/areas`);
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error('Error fetching areas:', error);
            return [];
        }
    },

    async addArea(areaData) {
        try {
            const response = await fetch(`${this.baseUrl}/areas/`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(areaData)
            });
            
            if (!response.ok) {
                const errData = await response.json();
                throw new Error(errData.detail || 'Failed to add area');
            }
            return await response.json();
        } catch (error) {
            console.error('Error adding area:', error);
            throw error;
        }
    },

    async getSensors() {
        try {
            const response = await fetch(`${this.baseUrl}/sensors`);
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error('Error fetching sensors:', error);
            return [];
        }
    },

    async addSensor(sensorData) {
        try {
            const response = await fetch(`${this.baseUrl}/sensors/`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(sensorData)
            });
            if (!response.ok) {
                const errData = await response.json();
                throw new Error(errData.detail || 'Failed to add sensor');
            }
            return await response.json();
        } catch (error) {
            console.error('Error adding sensor:', error);
            throw error;
        }
    },

    async updateSensor(id, sensorData) {
        try {
            const response = await fetch(`${this.baseUrl}/sensors/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(sensorData)
            });
            if (!response.ok) throw new Error('Failed to update sensor');
            return await response.json();
        } catch (error) {
            console.error('Error updating sensor:', error);
            throw error;
        }
    },

    async deleteSensor(id) {
        try {
            const response = await fetch(`${this.baseUrl}/sensors/${id}`, { method: 'DELETE' });
            if (!response.ok) throw new Error('Failed to delete sensor');
            return await response.json();
        } catch (error) {
            console.error('Error deleting sensor:', error);
            throw error;
        }
    },

    async getActuators() {
        try {
            const response = await fetch(`${this.baseUrl}/actuators`);
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error('Error fetching actuators:', error);
            return [];
        }
    },

    async addActuator(actuatorData) {
        try {
            const response = await fetch(`${this.baseUrl}/actuators/`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(actuatorData)
            });
            if (!response.ok) {
                const errData = await response.json();
                throw new Error(errData.detail || 'Failed to add actuator');
            }
            return await response.json();
        } catch (error) {
            console.error('Error adding actuator:', error);
            throw error;
        }
    },

    async updateActuator(id, actuatorData) {
        try {
            const response = await fetch(`${this.baseUrl}/actuators/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(actuatorData)
            });
            if (!response.ok) throw new Error('Failed to update actuator');
            return await response.json();
        } catch (error) {
            console.error('Error updating actuator:', error);
            throw error;
        }
    },

    async deleteActuator(id) {
        try {
            const response = await fetch(`${this.baseUrl}/actuators/${id}`, { method: 'DELETE' });
            if (!response.ok) throw new Error('Failed to delete actuator');
            return await response.json();
        } catch (error) {
            console.error('Error deleting actuator:', error);
            throw error;
        }
    },
    async toggleActuatorAuto(id, is_auto_enabled) {
        try {
            const response = await fetch(`${this.baseUrl}/actuators/${id}/toggle_auto`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ is_auto_enabled })
            });
            if (!response.ok) throw new Error('Failed to toggle auto state');
            return await response.json();
        } catch (error) {
            console.error('Error toggling auto state:', error);
            throw error;
        }
    },

    async triggerManualOverride(actuatorId, command) {
        try {
            const response = await fetch(`${this.baseUrl}/irrigation/override/${actuatorId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ command: command })
            });
            if (!response.ok) throw new Error('Failed to trigger actuator');
            return await response.json();
        } catch (error) {
            console.error('Error triggering actuator:', error);
            throw error;
        }
    },

    async getDataRequests() {
        try {
            const response = await fetch(`${this.baseUrl}/data-requests/`);
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error('Error fetching data requests:', error);
            return [];
        }
    },

    async getWaterUsage(hours = 24) {
        // Return zero data until backend endpoint is fully integrated
        return {
            total_discharged: 0,
            remaining: 0,
            history: []
        };
    },

    async updateAreaThresholds(areaId, data) {
        try {
            const response = await fetch(`${this.baseUrl}/areas/${areaId}/sensors/thresholds`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (!response.ok) throw new Error('Failed to update area thresholds');
            return await response.json();
        } catch (error) {
            console.error('Error updating area thresholds:', error);
            throw error;
        }
    },

    // --- Area Rules & Thresholds ---
    async getAreaConditions(areaId) {
        try {
            const response = await fetch(`${this.baseUrl}/areas/${areaId}/conditions`);
            if(!response.ok) throw new Error('Failed to fetch conditions');
            return await response.json();
        } catch (e) {
            console.error(e);
            return [];
        }
    },
    async addAreaCondition(areaId, data) {
        try {
            const response = await fetch(`${this.baseUrl}/areas/${areaId}/conditions`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if(!response.ok) throw new Error('Failed to add condition');
            return await response.json();
        } catch (e) {
            console.error(e);
            throw e;
        }
    },
    async deleteAreaCondition(areaId, conditionId) {
        try {
            const response = await fetch(`${this.baseUrl}/areas/${areaId}/conditions/${conditionId}`, {
                method: 'DELETE'
            });
            if(!response.ok) throw new Error('Failed to delete condition');
            return await response.json();
        } catch (e) {
            console.error(e);
            throw e;
        }
    },

    async getAreaSchedules(areaId) {
        try {
            const response = await fetch(`${this.baseUrl}/areas/${areaId}/schedules`);
            if(!response.ok) throw new Error('Failed to fetch schedules');
            return await response.json();
        } catch (e) {
            console.error(e);
            return [];
        }
    },
    async addAreaSchedule(areaId, data) {
        try {
            const response = await fetch(`${this.baseUrl}/areas/${areaId}/schedules`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if(!response.ok) throw new Error('Failed to add schedule');
            return await response.json();
        } catch (e) {
            console.error(e);
            throw e;
        }
    },
    async deleteAreaSchedule(areaId, scheduleId) {
        try {
            const response = await fetch(`${this.baseUrl}/areas/${areaId}/schedules/${scheduleId}`, {
                method: 'DELETE'
            });
            if(!response.ok) throw new Error('Failed to delete schedule');
            return await response.json();
        } catch (e) {
            console.error(e);
            throw e;
        }
    }
};
