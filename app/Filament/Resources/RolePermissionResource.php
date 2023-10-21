<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RolePermissionResource\Pages;
use App\Filament\Resources\RolePermissionResource\RelationManagers;
use App\Models\RolePermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionResource extends Resource
{
    protected static ?string $model = RolePermission::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('role_id')
                    ->label('Role')
                    ->required()
                    ->options(Role::all()->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('permission_id')
                    ->label('Permissions')
                    ->required()
                    ->multiple()
                    ->options(Permission::all()->pluck('name', 'id'))
                    ->searchable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('permission_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role_id')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRolePermissions::route('/'),
            'create' => Pages\CreateRolePermission::route('/create'),
            'view' => Pages\ViewRolePermission::route('/{record}'),
            'edit' => Pages\EditRolePermission::route('/{record}/edit'),
        ];
    }
}
