// Class pyramid component (Fan hierarchy visualization)
export default function classPyramid(grades = []) {
    const sorted = [...grades].sort((a, b) => b.fans - a.fans);
    const total = sorted.reduce((sum, g) => sum + (g.fans || 0), 0);
    const max = sorted.length > 0 ? sorted[0].fans : 1;

    return {
        sortedGrades: sorted,
        totalFans: total,
        maxFans: max,
        hoveredLayer: null,

        init() {},
    };
}
