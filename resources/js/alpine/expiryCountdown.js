// mode: 'expires' | 'recurring-active' | 'reactivates'
export const expiryCountdown = (timestamp, mode) => ({
    label: '',
    _target: new Date(timestamp).getTime(),

    init() {
        this.update();
        setInterval(() => this.update(), 1_000);
    },

    update() {
        const diffMs = this._target - Date.now();

        if (diffMs <= 0) {
            if (mode === 'reactivates')      this.label = 'Reactivating...';
            else if (mode === 'recurring-active') this.label = 'Active — restarting next cycle...';
            else                             this.label = 'Ending...';
            return;
        }

        const diffSec = Math.floor(diffMs / 1_000);
        const diffMin = Math.floor(diffSec / 60);
        const diffHrs = Math.floor(diffMin / 60);
        const diffDays = Math.floor(diffHrs / 24);

        let timeStr;
        if (diffDays > 0)      timeStr = `${diffDays}d ${diffHrs % 24}h`;
        else if (diffHrs > 0)  timeStr = `${diffHrs}h ${diffMin % 60}m`;
        else if (diffMin > 0)  timeStr = `${diffMin}m ${diffSec % 60}s`;
        else                   timeStr = `${diffSec}s`;

        if (mode === 'reactivates')           this.label = `Reactivates in ${timeStr}`;
        else if (mode === 'recurring-active') this.label = `Active — next cycle starts in ${timeStr}`;
        else                                  this.label = `Expires in ${timeStr}`;
    },
});
