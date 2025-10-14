export function buildVariationQuery(optionIds?: Record<string, number | null> | null): string {
	if (!optionIds) return '';
	const params = new URLSearchParams();
	Object.entries(optionIds).forEach(([typeId, optionId]) => {
		if (optionId != null) params.append(String(typeId), String(optionId));
	});
	const q = params.toString();
	return q ? `?${q}` : '';
}

