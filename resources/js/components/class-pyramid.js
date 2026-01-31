// Class pyramid component (Fan hierarchy visualization)
export default function classPyramid() {
    return {
        currentClass: "Pre-Open",
        fanCount: 0,
        classes: [
            { name: "Pre-Open", minFans: 0, maxFans: 999 },
            { name: "Open", minFans: 1000, maxFans: 4999 },
            { name: "Junior", minFans: 5000, maxFans: 9999 },
            { name: "Classic", minFans: 10000, maxFans: 29999 },
            { name: "Senior", minFans: 30000, maxFans: 59999 },
            { name: "URA Finals", minFans: 60000, maxFans: 999999 },
        ],

        init() {
            this.updateClass();
        },

        updateClass() {
            const classInfo = this.classes.find(
                (c) => this.fanCount >= c.minFans && this.fanCount <= c.maxFans,
            );
            if (classInfo) {
                this.currentClass = classInfo.name;
            }
        },

        setFanCount(count) {
            this.fanCount = count;
            this.updateClass();
        },

        getProgressToNextClass() {
            const currentIndex = this.classes.findIndex(
                (c) => c.name === this.currentClass,
            );
            if (
                currentIndex === -1 ||
                currentIndex === this.classes.length - 1
            ) {
                return 100;
            }

            const current = this.classes[currentIndex];
            const next = this.classes[currentIndex + 1];
            const progress =
                ((this.fanCount - current.minFans) /
                    (next.minFans - current.minFans)) *
                100;
            return Math.min(100, Math.max(0, progress));
        },

        getClassColor(className) {
            const colors = {
                "Pre-Open": "bg-gray-400",
                Open: "bg-blue-400",
                Junior: "bg-green-400",
                Classic: "bg-yellow-400",
                Senior: "bg-orange-400",
                "URA Finals": "bg-red-400",
            };
            return colors[className] || "bg-gray-400";
        },
    };
}
