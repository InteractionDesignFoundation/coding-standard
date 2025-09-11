@includeIf(true, 'admin.existing')
@includeIf(false, 'admin.missing')
@includeWhen($condition, 'layout.missing')